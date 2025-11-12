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
        // Pasa el AJAX URL para el JS
        wp_localize_script('mdt-events-js', 'mdtPlugin', [
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
    }
});

// REGISTRA EL AJAX HANDLER
add_action('wp_ajax_mdt_get_events', 'mdt_ajax_get_events');
function mdt_ajax_get_events() {
    $events = mdt_get_amelia_events();
    if ($events && is_array($events)) {
        ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Precio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?php echo esc_html($event['name'] ?? ''); ?></td>
                        <td><?php echo esc_html($event['periods'][0]['periodStart'] ?? ''); ?></td>
                        <td><?php echo esc_html($event['price'] ?? ''); ?>€</td>
                        <td>
                            <button data-id="<?php echo esc_attr($event['id']); ?>" class="mdt-edit-btn">Editar</button>
                            <button data-id="<?php echo esc_attr($event['id']); ?>" class="mdt-delete-btn">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "<p>No se encontraron eventos.</p>";
    }
    wp_die();
}
