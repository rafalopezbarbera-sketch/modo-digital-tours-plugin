/**
 * MDT Frontend JS - minimal steps implementation
 * - Busca wrappers inyectados por shortcode y monta el flow
 * - Llama a los endpoints REST proxy: /wp-json/modo-digital-tours/v1/events y /book
 *
 * Nota:
 * - Este script es un punto de partida; reemplaza el calendario simple por FullCalendar u otro componente
 * - Asegúrate de mapear correctamente los campos recibidos desde Amelia (nombres como startDate, prices, id, etc.)
 */

(function () {
  function getSiteData(counter) {
    return window['MDT_SiteData_' + counter] || {};
  }

  function mount(root, counter) {
    const siteData = getSiteData(counter);
    const restRoot = siteData.rest_root || (window.location.origin + '/wp-json/modo-digital-tours/v1');
    const nonce = siteData.nonce || '';

    root.innerHTML = `
      <div class="mdt-steps">
        <div class="mdt-step mdt-step-1" data-step="1">
          <h3>Encuentra tu fecha</h3>
          <div class="mdt-calendar"></div>
          <div class="mdt-day-events"></div>
        </div>
        <div class="mdt-step mdt-step-2" data-step="2" style="display:none;">
          <button class="mdt-back-to-step1">&larr; Volver</button>
          <h3>Datos personales</h3>
          <div class="mdt-selected-event"></div>
          <div class="mdt-ticket-rows"></div>
          <form class="mdt-personal-form">
            <input name="firstName" placeholder="Nombre" required />
            <input name="lastName" placeholder="Apellido" required />
            <input name="email" type="email" placeholder="Correo" required />
            <input name="phone" placeholder="Teléfono" />
            <button type="submit">Siguiente</button>
          </form>
        </div>
        <div class="mdt-step mdt-step-3" data-step="3" style="display:none;">
          <button class="mdt-back-to-step2">&larr; Volver</button>
          <h3>Pago</h3>
          <div class="mdt-payment-area"></div>
          <button class="mdt-pay-btn">Pagar / Confirmar</button>
        </div>
        <div class="mdt-step mdt-step-4" data-step="4" style="display:none;">
          <h3>Confirmación</h3>
          <div class="mdt-confirm"></div>
          <button class="mdt-new-book">Reservar otro tour</button>
        </div>
      </div>
    `;

    const calendarEl = root.querySelector('.mdt-calendar');
    const dayEventsEl = root.querySelector('.mdt-day-events');

    let selectedDate = null;
    let selectedEvent = null;
    let ticketSelection = {};
    let personalData = {};

    function goToStep(n) {
      root.querySelectorAll('.mdt-step').forEach(s => s.style.display = 'none');
      const el = root.querySelector('.mdt-step-' + n);
      if (el) el.style.display = '';
    }

    // Minimal calendar: request events by month from proxy and show days with events
    function loadEventsForMonth(year, month) {
      // Amelia expects different params; aqui pasamos month/year como ejemplo
      const url = restRoot + '/events?year=' + year + '&month=' + (month + 1);
      fetch(url)
        .then(r => r.json())
        .then(resp => {
          const events = (resp && resp.data) ? resp.data : [];
          const dayMap = {};
          events.forEach(ev => {
            // Ajusta el path al formato real devuelto por Amelia
            const date = (ev.startDate || ev.start || ev.periodStart || '').split('T')[0];
            if (!date) return;
            dayMap[date] = dayMap[date] || [];
            dayMap[date].push(ev);
          });

          let html = '<div class="mdt-days">';
          Object.keys(dayMap).sort().forEach(d => {
            html += `<button class="mdt-day-btn" data-day="${d}">${d}</button>`;
          });
          html += '</div>';
          calendarEl.innerHTML = html;

          calendarEl.querySelectorAll('.mdt-day-btn').forEach(btn => {
            btn.addEventListener('click', () => {
              selectedDate = btn.dataset.day;
              renderEventsForDay(dayMap[selectedDate] || []);
            });
          });
        })
        .catch(err => {
          calendarEl.innerHTML = '<div class="mdt-error">Error al cargar eventos</div>';
          console.error(err);
        });
    }

    function renderEventsForDay(events) {
      if (!events.length) {
        dayEventsEl.innerHTML = '<div>No hay eventos.</div>';
        return;
      }
      let html = '';
      events.forEach(ev => {
        const title = ev.title || ev.name || ev.serviceName || 'Evento';
        const start = ev.startDate || ev.start || ev.periodStart || '';
        const price = (ev.price != null) ? ev.price : (ev.prices && ev.prices[0] && ev.prices[0].price) || 'Gratis';
        html += `
          <div class="mdt-card" data-ev='${JSON.stringify(ev)}'>
            <div class="mdt-card-head">
              <div class="mdt-card-title">${title}</div>
              <div class="mdt-card-price">${price}</div>
            </div>
            <div class="mdt-card-body">
              <div class="mdt-card-start">${start}</div>
            </div>
            <div class="mdt-card-actions">
              <button class="mdt-reserve-btn">Reservar</button>
            </div>
          </div>
        `;
      });
      dayEventsEl.innerHTML = html;

      dayEventsEl.querySelectorAll('.mdt-reserve-btn').forEach(btn => {
        btn.addEventListener('click', (ev) => {
          const card = ev.target.closest('.mdt-card');
          selectedEvent = JSON.parse(card.dataset.ev);
          goToStep(2);
          renderStep2(selectedEvent);
        });
      });
    }

    function renderStep2(event) {
      const selEl = root.querySelector('.mdt-selected-event');
      selEl.innerHTML = `<div class="mdt-selected">
        <strong>${event.title || event.name}</strong><div>${event.startDate || event.start || ''}</div>
      </div>`;

      const ticketRowsEl = root.querySelector('.mdt-ticket-rows');
      ticketSelection = {};
      const prices = event.prices || event.ticketTypes || (event.price ? [{ id: 'default', name: 'Entrada', price: event.price }] : [{ id: 'default', name: 'Entrada', price: 0 }]);
      let html = '<div class="mdt-tickets">';
      prices.forEach(p => {
        const id = p.id || p.priceId || (p.name + (p.price || ''));
        ticketSelection[id] = 0;
        html += `<div class="mdt-ticket-row" data-id="${id}">
          <div class="mdt-ticket-name">${p.name || 'Entrada'}</div>
          <div class="mdt-ticket-price">${p.price != null ? p.price : (p.amount || 0)}€</div>
          <div class="mdt-controls"><button class="mdt-minus">-</button><span class="mdt-qty">0</span><button class="mdt-plus">+</button></div>
        </div>`;
      });
      html += '</div>';
      ticketRowsEl.innerHTML = html;

      ticketRowsEl.querySelectorAll('.mdt-ticket-row').forEach(row => {
        const id = row.dataset.id;
        row.querySelector('.mdt-plus').addEventListener('click', () => {
          ticketSelection[id] = (ticketSelection[id] || 0) + 1;
          row.querySelector('.mdt-qty').textContent = ticketSelection[id];
        });
        row.querySelector('.mdt-minus').addEventListener('click', () => {
          ticketSelection[id] = Math.max(0, (ticketSelection[id] || 0) - 1);
          row.querySelector('.mdt-qty').textContent = ticketSelection[id];
        });
      });

      // personal form
      const form = root.querySelector('.mdt-personal-form');
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(form);
        personalData = {
          firstName: fd.get('firstName'),
          lastName: fd.get('lastName'),
          email: fd.get('email'),
          phone: fd.get('phone'),
        };
        goToStep(3);
        renderStep3();
      });

      // back button from step 2
      root.querySelector('.mdt-back-to-step1').addEventListener('click', () => {
        goToStep(1);
      });
    }

    function renderStep3() {
      const payArea = root.querySelector('.mdt-payment-area');
      let total = 0;
      const breakdown = [];
      const prices = selectedEvent.prices || selectedEvent.ticketTypes || [{ id: 'default', name: 'Entrada', price: selectedEvent.price || 0 }];
      const priceMap = {};
      prices.forEach(p => {
        const id = p.id || p.priceId || (p.name + (p.price || ''));
        priceMap[id] = p;
      });
      Object.keys(ticketSelection).forEach(id => {
        const qty = ticketSelection[id] || 0;
        if (qty > 0) {
          const p = priceMap[id] || { price: 0, name: id };
          const priceN = parseFloat(p.price || p.amount || 0) || 0;
          breakdown.push({ name: p.name || id, qty: qty, price: priceN, subtotal: priceN * qty });
          total += priceN * qty;
        }
      });

      let html = '<div class="mdt-breakdown">';
      breakdown.forEach(b => {
        html += `<div>${b.qty} x ${b.name} — ${b.subtotal.toFixed(2)}€</div>`;
      });
      html += `<div><strong>Total: ${total.toFixed(2)}€</strong></div>`;
      if (total === 0) {
        html += '<div>Evento gratuito. Pulsar "Pagar" finalizará la reserva sin pago.</div>';
      }
      html += '</div>';
      payArea.innerHTML = html;

      // back to step 2
      root.querySelector('.mdt-back-to-step2').addEventListener('click', () => {
        goToStep(2);
      });

      // pay button
      root.querySelector('.mdt-pay-btn').addEventListener('click', () => {
        submitBooking(total, breakdown);
      });
    }

    function submitBooking(total, breakdown) {
      // Construye payload aproximado. Debes adaptar los campos al contrato de la API de Amelia.
      const payload = {
        eventId: selectedEvent.id || selectedEvent.eventId || selectedEvent.key,
        date: selectedDate,
        tickets: Object.keys(ticketSelection).map(id => ({ priceId: id, quantity: ticketSelection[id] })),
        customer: personalData,
        total: total
      };

      fetch(restRoot + '/book', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': siteData.nonce || ''
        },
        body: JSON.stringify(payload)
      })
      .then(r => r.json())
      .then(resp => {
        if (resp && resp.code && resp.code >= 200 && resp.code < 300) {
          goToStep(4);
          const confirmEl = root.querySelector('.mdt-confirm');
          confirmEl.innerHTML = `<div class="mdt-success">¡Reserva completada!</div><pre>${JSON.stringify(resp.data, null, 2)}</pre>`;
        } else {
          console.error('Booking error', resp);
          alert('Error al crear la reserva. Revisa consola.');
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error de red al crear la reserva.');
      });
    }

    // init: load current month
    const d = new Date();
    loadEventsForMonth(d.getFullYear(), d.getMonth());
    goToStep(1);

    // new booking button
    root.addEventListener('click', function (e) {
      if (e.target.matches('.mdt-new-book')) {
        // reset flow
        selectedDate = null;
        selectedEvent = null;
        ticketSelection = {};
        personalData = {};
        loadEventsForMonth(new Date().getFullYear(), new Date().getMonth());
        goToStep(1);
      }
    });
  }

  // Auto mount all roots on DOMContentLoaded
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.mdt-root, .mdt-list-root').forEach(root => {
      const counter = root.dataset.counter || '1';
      mount(root, counter);
    });
  });
})();
