<?php
function mdt_events_dashboard_page() {
    echo '<div class="wrap">';
    echo '<h1>Gestión de Eventos</h1>';
    include plugin_dir_path(__FILE__) . 'templates/events-list.php';
    echo '</div>';
}
