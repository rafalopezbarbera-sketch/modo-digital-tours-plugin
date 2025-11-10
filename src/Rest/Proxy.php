<?php
namespace ModoDigitalTours\Rest;

class Proxy
{
    const OPTION_BASE = 'mdt_amelia_base_url';
    const OPTION_KEY = 'mdt_amelia_api_key';

    public static function registerRoutes()
    {
        register_rest_route('modo-digital-tours/v1', '/events', [
            'methods' => 'GET',
            'callback' => [self::class, 'getEvents'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('modo-digital-tours/v1', '/book', [
            'methods' => 'POST',
            'callback' => [self::class, 'createBooking'],
            'permission_callback' => [self::class, 'permissionForBooking'],
        ]);
    }

    private static function getBase()
    {
        $base = get_option(self::OPTION_BASE, '');
        return rtrim($base, '/');
    }

    private static function getApiKey()
    {
        return get_option(self::OPTION_KEY, '');
    }

    public static function getEvents(\WP_REST_Request $request)
    {
        $base = self::getBase();
        if (empty($base)) {
            return new \WP_Error('no_base', 'Amelia base URL not configured', ['status' => 500]);
        }

        // Build Amelia admin-ajax URL for API calls:
        // Example: https://your_site/wp-admin/admin-ajax.php?action=wpamelia_api&call=/api/v1/events
        $query = $request->get_query_params();
        $callPath = '/api/v1/events'; // ajusta según la documentación si diferente
        $ajaxUrl = $base . '/wp-admin/admin-ajax.php?action=wpamelia_api&call=' . rawurlencode($callPath);

        if (!empty($query)) {
            $ajaxUrl = add_query_arg($query, $ajaxUrl);
        }

        $headers = [];
        $apiKey = self::getApiKey();
        if ($apiKey) {
            $headers['Amelia'] = $apiKey; // API key header as documented
        }

        $response = wp_remote_get($ajaxUrl, [
            'headers' => $headers,
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            return new \WP_Error('http_error', $response->get_error_message(), ['status' => 500]);
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        return rest_ensure_response([
            'code' => $code,
            'data' => $decoded,
            'raw' => $body
        ]);
    }

    public static function createBooking(\WP_REST_Request $request)
    {
        $base = self::getBase();
        if (empty($base)) {
            return new \WP_Error('no_base', 'Amelia base URL not configured', ['status' => 500]);
        }

        $payload = $request->get_json_params();

        // Amelia booking endpoint path: /api/v1/bookings (ajusta si tu versión difiere)
        $callPath = '/api/v1/bookings';
        $ajaxUrl = $base . '/wp-admin/admin-ajax.php?action=wpamelia_api&call=' . rawurlencode($callPath);

        $headers = [
            'Content-Type' => 'application/json'
        ];
        $apiKey = self::getApiKey();
        if ($apiKey) {
            $headers['Amelia'] = $apiKey;
        }

        $response = wp_remote_post($ajaxUrl, [
            'headers' => $headers,
            'body' => wp_json_encode($payload),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return new \WP_Error('http_error', $response->get_error_message(), ['status' => 500]);
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        return rest_ensure_response([
            'code' => $code,
            'data' => $decoded,
            'raw' => $body
        ]);
    }

    public static function permissionForBooking()
    {
        // Allow unauthenticated users to create bookings from frontend but verify nonce
        $nonce = isset($_SERVER['HTTP_X_WP_NONCE']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_WP_NONCE'])) : '';
        if (empty($nonce)) {
            return false;
        }
        return wp_verify_nonce($nonce, 'wp_rest');
    }
}
