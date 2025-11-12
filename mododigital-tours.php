<?php
/*
Plugin Name: Modo Digital Tours
Description: Panel whitelabel para gestionar eventos de Amelia Booking.
Author: mododigital.es
Version: 1.0
*/

require_once plugin_dir_path(__FILE__) . 'admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'admin-page-events.php';
require_once plugin_dir_path(__FILE__) . 'includes/amelia-events-api.php';

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'toplevel_page_mododigital_tours_events') {
        wp_enqueue_script(
            'mdt-events-js',
            plugin_dir_url(__FILE__) . 'assets/js/events-dashboard.js',
            ['jquery'],
            '1.0',
            true
        );
        wp_enqueue_style(
            'mdt-events-css',
            plugin_dir_url(__FILE__) . 'assets/css/events-dashboard.css'
        );
    }
});
