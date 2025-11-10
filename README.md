```markdown
# Modo Digital Tours (Plugin skeleton)

Descripción:
Este plugin crea shortcodes personalizados que consumen la API de Amelia (vía proxy server-side) para renderizar un flujo de reserva "por steps" (calendario → lista → datos → pago → confirmación).

Instalación:
1. Copia la carpeta `modo-digital-tours` a `wp-content/plugins/`.
2. Activa el plugin desde el panel de administración de WordPress.
3. Ve a Ajustes > Modo Digital Tours y configura:
   - Amelia Site URL: la URL base donde está Amelia (ej: https://tusitio.com)
   - Amelia API Key: la API Key proporcionada por Amelia (se enviará en la cabecera "Amelia")

Shortcodes:
- [mdt_eventscalendar] → renderiza el calendario y flujo completo.
- [mdt_eventslist] → renderiza una vista tipo lista (plantilla de ejemplo).

Endpoints REST (proxy):
- GET /wp-json/modo-digital-tours/v1/events → reenvía a Amelia para obtener eventos.
- POST /wp-json/modo-digital-tours/v1/book → crea reserva (proxy a Amelia). Requiere nonce en `X-WP-Nonce`.

Notas importantes:
- Este es un esqueleto funcional que requiere:
  - Mapear y adaptar los nombres de campos a la estructura exacta que devuelva tu instancia de Amelia (ej.: `startDate`, `prices`, `id`, etc.)
  - Integrar el método de pago a través de la respuesta que la API de Amelia ofrezca para pagos (Stripe) — normalmente Amelia dará datos para iniciar Payment Intent o Checkout.
  - Reemplazar el calendario minimal por una librería como FullCalendar para una UI más rica.
  - Añadir validaciones, UX y estilos finales según tu diseño.
```
