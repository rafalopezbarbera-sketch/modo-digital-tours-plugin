<?php
namespace ModoDigitalTours\Shortcode;

class EventsShortcode
{
    private static $counter = 1;

    public static function calendarShortcodeHandler($atts)
    {
        $params = shortcode_atts([
            'tag' => '',
            'location' => '',
            'counter' => self::$counter++,
        ], $atts);

        // enqueue assets
        wp_enqueue_script('mdt-frontend');
        wp_enqueue_style('mdt-frontend-css');

        // prepare data for script
        $data = [
            'counter' => $params['counter'],
            'params' => $params,
            'rest_root' => esc_url_raw(rest_url('modo-digital-tours/v1')),
            'nonce' => wp_create_nonce('wp_rest')
        ];
        wp_localize_script('mdt-frontend', 'MDT_SiteData_' . $params['counter'], $data);

        ob_start();
        include __DIR__ . '/../../view/frontend/calendar.inc.php';
        return ob_get_clean();
    }

    public static function listShortcodeHandler($atts)
    {
        $params = shortcode_atts([
            'tag' => '',
            'location' => '',
            'counter' => self::$counter++,
        ], $atts);

        wp_enqueue_script('mdt-frontend');
        wp_enqueue_style('mdt-frontend-css');

        $data = [
            'counter' => $params['counter'],
            'params' => $params,
            'rest_root' => esc_url_raw(rest_url('modo-digital-tours/v1')),
            'nonce' => wp_create_nonce('wp_rest')
        ];
        wp_localize_script('mdt-frontend', 'MDT_SiteData_' . $params['counter'], $data);

        ob_start();
        include __DIR__ . '/../../view/frontend/list.inc.php';
        return ob_get_clean();
    }
}
