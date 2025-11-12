<?php
// Funciones helper para comunicar con la API REST de Amelia
function mdt_get_amelia_events() {
    $response = wp_remote_get( home_url('/wp-json/amelia/v1/events') );
    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}
function mdt_create_amelia_event($data) {
    $response = wp_remote_post( home_url('/wp-json/amelia/v1/events'), [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode($data)
    ]);
    return wp_remote_retrieve_body($response);
}
function mdt_update_amelia_event($id, $data) {
    $response = wp_remote_request( home_url('/wp-json/amelia/v1/events/' . $id), [
        'method'  => 'PUT',
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode($data)
    ]);
    return wp_remote_retrieve_body($response);
}
function mdt_delete_amelia_event($id) {
    $response = wp_remote_request( home_url('/wp-json/amelia/v1/events/' . $id), [
        'method' => 'DELETE'
    ]);
    return wp_remote_retrieve_body($response);
}
