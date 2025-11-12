<?php
add_action('admin_menu', function() {
    add_menu_page(
        'Eventos Modo Digital',     // Título de la página
        'Modo Digital Tours',       // Título del menú
        'manage_options',           // Capacidad
        'mododigital_tours_events', // Slug
        'mdt_events_dashboard_page',// Callback
        'dashicons-calendar-alt',   // Icono
        21                         // Posición
    );
});
