<?php
/**
 * Plugin Name: Modo Digital Tours
 * Plugin URI:  https://mododigital.es
 * Description: Integra y crea shortcodes personalizados para eventos de Amelia con UI por steps. Autor: mododigital.es
 * Version:     0.1
 * Author:      mododigital.es
 * Text Domain: modo-digital-tours
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/src/Admin/Settings.php';
require_once __DIR__ . '/src/Shortcode/EventsShortcode.php';
require_once __DIR__ . '/src/Rest/Proxy.php';

use ModoDigitalTours\Admin\Settings;
use ModoDigitalTours\Shortcode\EventsShortcode;
use ModoDigitalTours\Rest\Proxy;

// Register assets and initialize
add_action('init', function () {
    // register scripts and styles
    wp_register_script(
        'mdt-frontend',
        plugins_url('public/js/mdt-frontend.js', __FILE__),
        ['wp-api', 'jquery'],
        '0.1',
        true
    );
    wp_register_style(
        'mdt-frontend-css',
        plugins_url('public/css/mdt-frontend.css', __FILE__),
        [],
        '0.1'
    );
});

// admin settings page
add_action('admin_menu', [Settings::class, 'addAdminMenu']);
add_action('admin_init', [Settings::class, 'registerSettings']);

// rest proxy routes
add_action('rest_api_init', [Proxy::class, 'registerRoutes']);

// Shortcodes
add_shortcode('mdt_eventscalendar', [EventsShortcode::class, 'calendarShortcodeHandler']);
add_shortcode('mdt_eventslist', [EventsShortcode::class, 'listShortcodeHandler']);
