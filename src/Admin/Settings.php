<?php
namespace ModoDigitalTours\Admin;

class Settings
{
    const OPTION_GROUP = 'mdt_settings_group';
    const OPTION_KEY_BASE = 'mdt_amelia_base_url';
    const OPTION_KEY_API = 'mdt_amelia_api_key';

    public static function addAdminMenu()
    {
        add_options_page(
            'Modo Digital Tours',
            'Modo Digital Tours',
            'manage_options',
            'modo-digital-tours',
            [self::class, 'settingsPage']
        );
    }

    public static function registerSettings()
    {
        register_setting(self::OPTION_GROUP, self::OPTION_KEY_BASE, [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        ]);
        register_setting(self::OPTION_GROUP, self::OPTION_KEY_API, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ]);

        add_settings_section('mdt_section_main', 'Configuración de Amelia', null, 'modo-digital-tours');

        add_settings_field(
            self::OPTION_KEY_BASE,
            'Amelia Site URL',
            [self::class, 'fieldBaseUrl'],
            'modo-digital-tours',
            'mdt_section_main'
        );

        add_settings_field(
            self::OPTION_KEY_API,
            'Amelia API Key',
            [self::class, 'fieldApiKey'],
            'modo-digital-tours',
            'mdt_section_main'
        );
    }

    public static function fieldBaseUrl()
    {
        $val = get_option(self::OPTION_KEY_BASE, '');
        printf(
            '<input type="text" name="%1$s" value="%2$s" class="regular-text" placeholder="https://tusitio.com">',
            esc_attr(self::OPTION_KEY_BASE),
            esc_attr($val)
        );
        echo '<p class="description">Introduce la URL base del sitio donde está Amelia (ej: https://tusitio.com)</p>';
    }

    public static function fieldApiKey()
    {
        $val = get_option(self::OPTION_KEY_API, '');
        printf(
            '<input type="text" name="%1$s" value="%2$s" class="regular-text">',
            esc_attr(self::OPTION_KEY_API),
            esc_attr($val)
        );
        echo '<p class="description">Introduce la API Key generada en Amelia. Será enviada en el header "Amelia".</p>';
    }

    public static function settingsPage()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Modo Digital Tours - Ajustes</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections('modo-digital-tours');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
