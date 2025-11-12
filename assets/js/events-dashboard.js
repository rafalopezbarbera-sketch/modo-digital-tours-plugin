// Simple: obtiene eventos y los pinta
jQuery(document).ready(function($) {
  function getEvents() {
    $.get(mdtPlugin.ajaxurl, { action: 'mdt_get_events' }, function(data) {
      $('#mdt-events-app').html(data);
    });
  }
  getEvents();
  // TODO: implementar popups/CRUD con AJAX para crear/editar/borrar
});
