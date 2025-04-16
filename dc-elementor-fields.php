<?php

/**
* Plugin Name: DC Elementor Fields
* Plugin URI: https://github.com/dynamiccreative/dc-elementor-fields
* Update URI: https://github.com/dynamiccreative/dc-elementor-fields
* Description: Ajoute des nouveaux types de champs dans Elementor Forms
* Version: 1.1.1
* Author: @tekno - dynamic creative - © 2025 - tous droits réservés
* Author URI: https://www.dynamic-creative.com
*/

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;
use ElementorPro\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

define( 'DEF_FILE', __FILE__ );
define( 'DEF_BASE', plugin_basename( __FILE__ ) );
define( 'DEF_DIR_PATH', plugin_dir_path( DEF_FILE ) );
define( 'DEF_DIR_URL', plugin_dir_url( DEF_FILE ) );
define( 'DEF_ASSETS', trailingslashit( DEF_DIR_URL . 'assets' ) );

class Dc_Elementor_Fields {

    public function __construct() {
        $this->include_files();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action( 'elementor_pro/forms/fields/register', [$this, 'register_field'] );
        
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);

        add_filter( 'plugin_action_links_' . DEF_BASE, [$this, 'def_settings_link'], 10, 2 );
    }

    public function include_files() {

        require_once DEF_DIR_PATH . 'includes/helper.php';
        /*extensions*/
        require_once DEF_DIR_PATH . 'includes/extensions/icons.php';
        /**/
        require_once DEF_DIR_PATH . 'includes/update-plugin.php';

    }

    public function register_field($form_fields_registrar) {

        require_once(__DIR__ . '/includes/fields/city-autocomplete-field.php');
        $form_fields_registrar->register( new \City_Autocomplete_Field() );

        require_once(__DIR__ . '/includes/fields/select2.php');
        $form_fields_registrar->register( new \Custom_Select2_Field() );
        
    }
    

    public function enqueue_scripts() {
        // autocomplete
        if (class_exists('\ElementorPro\Modules\Forms\Module')) {
            $api_key = get_option('def_google_api_key', '');
            
            if ($api_key) {
                $country = get_option('def_country_restriction', 'all');
                $region_param = ($country !== 'all') ? "&region={$country}" : '';

                wp_enqueue_script('google-maps-api', "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places,geometry{$region_param}", [], null, true);
                
                wp_enqueue_script('city-autocomplete-script', plugin_dir_url(__FILE__) . 'assets/js/city-autocomplete.js', ['jquery', 'google-maps-api'], '1.1', true);

                /* parameters to js */
                $params = array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'api_key' => $api_key,
                    'country' => $country,
                    'plugin_directory' => DEF_DIR_URL
                );
                wp_localize_script('city-autocomplete-script', 'monObjetJs', $params);
            }
        }

        // select2
        wp_enqueue_style('select2','https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);  
        wp_add_inline_script('select2', 'jQuery(document).ready(function($) {$(".custom-select2-field").each(function() {if (!$(this).hasClass("select2-hidden-accessible")) { $(this).select2({ placeholder: $(this).data("placeholder"), allowClear: true, width: "100%" }); } }); });'
        );
        wp_add_inline_style('select2','.select2-container--default .select2-selection--single {min-height:40px;} .select2-container--default .select2-selection--single .select2-selection__rendered {height:100%; padding-top: 5px;} .select2-container--default .select2-selection--single .select2-selection__arrow {top:50%; transform:translateY(-50%);} .select2 button.select2-selection__clear {color:#ccc;}');

        // icons
        wp_enqueue_script('icons-form', plugin_dir_url(__FILE__) . 'assets/js/icons-form.js', ['jquery'], '1.1', true);
        wp_enqueue_style('icons-form', plugin_dir_url(__FILE__) . 'assets/css/icons-form.css', [], '1.1');
    }
    
    public function add_settings_page() {
        add_options_page(
            'Fields Settings',
            'DC Elementor Fields',
            'manage_options',
            'def-settings',
            [$this, 'render_settings_page']
        );
    }
    
    public function register_settings() {
        register_setting(
            'def_settings_group',
            'def_google_api_key',
            ['sanitize_callback' => 'sanitize_text_field']
        );

        register_setting(
            'def_settings_group',
            'def_country_restriction',
            ['sanitize_callback' => 'sanitize_text_field']
        );
        
        add_settings_section(
            'def_main_section',
            'Google API Settings',
            null,
            'def-settings'
        );
        
        add_settings_field(
            'def_google_api_key',
            'Google API Key',
            [$this, 'render_api_key_field'],
            'def-settings',
            'def_main_section'
        );

        add_settings_field(
            'def_country_restriction',
            'Country Restriction',
            [$this, 'render_country_field'],
            'def-settings',
            'def_main_section'
        );
    }
    
    public function render_api_key_field() {
        $api_key = get_option('def_google_api_key', '');
        ?>
        <input type="text" 
               name="def_google_api_key" 
               value="<?php echo esc_attr($api_key); ?>" 
               class="regular-text" />
        <p class="description">Entrez votre clé API Google Maps (avec Places API activé).</p>
        <?php
    }

    public function render_country_field() {
        $country = get_option('def_country_restriction', 'all');
        $countries = [
            'all' => 'Tous les pays',
            'fr' => 'France',
            'us' => 'États-Unis',
            'gb' => 'Royaume-Uni',
            'ca' => 'Canada',
            'de' => 'Allemagne',
            // Ajoutez d'autres pays selon vos besoins
        ];
        ?>
        <select name="def_country_restriction">
            <?php foreach ($countries as $code => $name) : ?>
                <option value="<?php echo esc_attr($code); ?>" <?php selected($country, $code); ?>>
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">Restriction pays</p>
        <?php
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Google Autocomplete Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('def_settings_group');
                do_settings_sections('def-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function def_settings_link( array $links ) {
        $url = get_admin_url() . "admin.php?page=def-settings";
        $settings_link = '<a href="' . $url . '">' . __('Settings', 'geo-prestations') . '</a>';
        $links[] = $settings_link;
        return $links;
    }
    /***/
    /*public function update_elementor_control($widget, $control_name, $callback)
    {
        $elementor = \ElementorPro\Plugin::elementor();
        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), $control_name);
        if (is_wp_error($control_data)) {
            return;
        }
        $control_data = $callback($control_data);
        $widget->update_control($control_name, $control_data);
    }*/
    
}

function run_dc_elementor_fields() {
    $plugin = new Dc_Elementor_Fields();
}
add_action('plugins_loaded', 'run_dc_elementor_fields');
