<?php
/**
* Plugin Name: DC Elementor Fields
* Plugin URI: https://github.com/dynamiccreative/dc-elementor-fields
* Update URI: https://github.com/dynamiccreative/dc-elementor-fields
* Description: Ajoute des nouveaux types de champs dans Elementor Forms
* Version: 1.3.4
* Author: Team dynamic creative
* Author URI: https://www.dynamic-creative.com
* Primary Branch: main
* Domain Path: /languages
* Text Domain: dc-elementor-fields
* Tested up to:       6.8
* Requires at least:  6.7
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
define( 'DEF_VERSION', '1.3.4' );
define( 'DEF_PAGE_SLUG', 'def-settings' );
define( 'DEF_NONCE_ACTION', 'def_api_keys' );

class Dc_Elementor_Fields {

     private $config = [
        'slug'          => 'dc-elementor-fields/dc-elementor-fields.php',
        'repo'          => 'dc-elementor-fields',
        'access_token'  => '',
        'icon_url'      => 'https://raw.githubusercontent.com/dynamiccreative/dc-scroll-top/main/assets/img/icon-256x256.png',
        'banner_url'      => 'https://raw.githubusercontent.com/dynamiccreative/dc-scroll-top/main/assets/img/banner-1544x500.png',
    ];

    public function __construct() {
        $this->include_files();
        $this->update_plugin();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action( 'elementor_pro/forms/fields/register', [$this, 'register_field'] );

        add_action('admin_menu', [$this, 'add_settings_page'], 20);
        add_action('admin_init', [$this, 'register_settings']);

        add_action('admin_enqueue_scripts', [$this, 'load_admin_assets']);

        // Handlers sécurisés pour la clé API Google (jamais via options.php).
        add_action('admin_post_def_save_google_api_key',   [$this, 'handle_save_google_api_key']);
        add_action('admin_post_def_delete_google_api_key', [$this, 'handle_delete_google_api_key']);

        add_filter( 'plugin_action_links_' . DEF_BASE, [$this, 'def_settings_link'], 10, 2 );

        $def_icons = get_option('def_extension_icons', false);
        if ($def_icons) add_filter( 'plugin_row_meta', [ $this, 'icon_row_meta' ], 10, 4 );
    }

    public function load_admin_assets( $hook ) {
        // On charge les assets uniquement sur la page de réglages du plugin.
        if ( ! is_string( $hook ) || strpos( $hook, DEF_PAGE_SLUG ) === false ) {
            return;
        }
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'def-admin-style', DEF_DIR_URL . 'assets/css/admin.css', [], DEF_VERSION );
        wp_enqueue_script( 'def-admin-script', DEF_DIR_URL . 'assets/js/admin.js', [], DEF_VERSION, true );
    }

    public function include_files() {
        require_once DEF_DIR_PATH . 'includes/helper.php';
        require_once DEF_DIR_PATH . 'includes/widget-list.php';

        /*extensions*/
        $all_widgets = DEF_Elementor_Widget_List::instance()->get_list();
        if ( ! empty( $all_widgets ) && is_array( $all_widgets ) ) {
            foreach ( $all_widgets as $widget ) {
                $check = get_option($widget['name'], false);
                if ( 'extension' == $widget['type'] && $check) {
                    require_once DEF_DIR_PATH . 'includes/extensions/'. $widget['slug'] . '.php';
                }
            }
        }
    }

    public function update_plugin() {
        require_once DEF_DIR_PATH . 'includes/GitHubUpdater.php';
        $gitHubUpdater = new DefGitHubUpdater(DEF_FILE);
        $gitHubUpdater->setPluginIcon($this->config['icon_url']);
        $gitHubUpdater->setPluginBannerSmall($this->config['banner_url']);
        $gitHubUpdater->setPluginBannerLarge($this->config['banner_url']);
        $gitHubUpdater->setChangelog('CHANGELOG.md');
        $gitHubUpdater->add();
    }

    public function register_field($form_fields_registrar) {
        $all_widgets = DEF_Elementor_Widget_List::instance()->get_list();

        if ( ! empty( $all_widgets ) && is_array( $all_widgets ) ) {
            foreach ( $all_widgets as $widget ) {
                $check = get_option($widget['name'], false);
                if ( 'widget' == $widget['type'] && $check) {
                    require_once (__DIR__ . '/includes/fields/' . $widget['slug'] . '.php');
                    $class_name = '\\'. $this->make_classname($widget['function']);
                    if ( class_exists( $class_name ) ) {
                        $form_fields_registrar->register( new $class_name() );
                    }
                }
            }
        }
    }

    public static function make_classname( $dirname ) {
        $dirname    = pathinfo( $dirname, PATHINFO_FILENAME );
        $class_name = explode( '_', $dirname );
        $class_name = array_map( 'ucfirst', $class_name );
        $class_name = implode( '_', $class_name );

        return $class_name;
    }

    /*
     * Ajoute une icone à droite da la version dans la vue liste
     */
    public function icon_row_meta($links, $file, $plugin_data, $status) {
        if ($this->config['slug'] === $file) {
            $links[] = '<a href="'.esc_attr($plugin_data['PluginURI']).'" class="" target="_blank"><img src="' . esc_url( $this->config['icon_url'] ) . '" alt="Icon" style="width:16px;height:16px;vertical-align:middle;"/></a>';
        }
        return $links;
    }

    /**/
    public function enqueue_scripts() {
        $def_city = get_option('def_city_autocomplete_field', false);
        if ($def_city) {
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
        }

        $def_select2      = get_option('def_select2', false);
        $def_select_posts = get_option('def_select_posts', false);
        if ($def_select2 || $def_select_posts) {
            // select2 (chargé aussi pour le champ Select CPT afin d'activer l'autocomplétion côté client)
            wp_enqueue_style('select2','https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
            wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
            wp_add_inline_script('select2', 'jQuery(document).ready(function($) {$(".custom-select2-field").each(function() {if (!$(this).hasClass("select2-hidden-accessible")) { $(this).select2({ placeholder: $(this).data("placeholder"), allowClear: true, width: "100%" }); } }); });'
            );
            wp_add_inline_style('select2','.select2-container--default .select2-selection--single {min-height:40px;} .select2-container--default .select2-selection--single .select2-selection__rendered {height:100%; padding-top: 5px;} .select2-container--default .select2-selection--single .select2-selection__arrow {top:50%; transform:translateY(-50%);} .select2 button.select2-selection__clear {color:#ccc;}');
        }

        $def_icons = get_option('def_extension_icons', false);

        if ($def_icons) {
            // icons
            wp_enqueue_script('icons-form', plugin_dir_url(__FILE__) . 'assets/js/icons-form.js', ['jquery'], '1.1', true);
            wp_enqueue_style('icons-form', plugin_dir_url(__FILE__) . 'assets/css/icons-form.css', [], '1.1');
        }
    }

    public function add_settings_page() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( is_plugin_active('dc-support-technique/dc-support-technique.php') ) {
            add_submenu_page(
                'dc-settings',
                'Fields Settings',
                'Elementor Fields',
                'manage_options',
                DEF_PAGE_SLUG,
                [$this, 'render_settings_page'],
                30
            );
        } else {
            add_options_page(
                'Fields Settings',
                'DC Elementor Fields',
                'manage_options',
                DEF_PAGE_SLUG,
                [$this, 'render_settings_page']
            );
        }
    }

    /**
     * Settings non-secrets uniquement (country + toggles).
     * La clé API Google est volontairement exclue : gérée par admin-post.php.
     */
    public function register_settings() {
        register_setting(
            'def_settings_group',
            'def_country_restriction',
            ['sanitize_callback' => 'sanitize_text_field']
        );

        $all_widgets = DEF_Elementor_Widget_List::instance()->get_list();
        if ( ! empty( $all_widgets ) && is_array( $all_widgets ) ) {
            foreach ( $all_widgets as $widget ) {
                register_setting(
                    'def_settings_group',
                    $widget['name'],
                    [
                        'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                        'default'           => '',
                    ]
                );
            }
        }
    }

    public function sanitize_checkbox( $value ) {
        return ( $value === '1' || $value === 1 || $value === true ) ? '1' : '';
    }

    /* ============================================================
     * Helpers sécurité (clés API)
     * ============================================================ */

    /**
     * Masque une clé pour affichage : largeur constante, 4 derniers caractères révélés.
     */
    public static function mask_api_key( $key ) {
        $key = (string) $key;
        if ( $key === '' ) return '';
        if ( strlen( $key ) <= 4 ) return str_repeat( '•', strlen( $key ) );
        return '••••••••••••' . substr( $key, -4 );
    }

    /**
     * Retire toute clé reconnue d'un message (logs, notices).
     */
    public static function redact_keys( $message ) {
        if ( ! is_string( $message ) || $message === '' ) return $message;
        return preg_replace(
            [
                '/AIza[A-Za-z0-9_\-]{10,}/',
                '/sk-ant-[A-Za-z0-9_\-]{10,}/i',
                '/sk-[A-Za-z0-9_\-]{10,}/i',
                '/Bearer\s+[A-Za-z0-9_.\-]{20,}/i',
            ],
            [
                'AIza***REDACTED***',
                'sk-ant-***REDACTED***',
                'sk-***REDACTED***',
                'Bearer ***REDACTED***',
            ],
            $message
        );
    }

    public function settings_page_url() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( is_plugin_active('dc-support-technique/dc-support-technique.php') ) {
            return admin_url( 'admin.php?page=' . DEF_PAGE_SLUG );
        }
        return admin_url( 'options-general.php?page=' . DEF_PAGE_SLUG );
    }

    /* ============================================================
     * Handlers admin-post pour la clé API Google
     * ============================================================ */

    public function handle_save_google_api_key() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permissions insuffisantes', 'Erreur', [ 'response' => 403 ] );
        }
        check_admin_referer( DEF_NONCE_ACTION );

        $redirect = $this->settings_page_url();

        $value = isset( $_POST['api_key'] ) ? (string) wp_unslash( $_POST['api_key'] ) : '';
        $value = preg_replace( '/[\r\n\t ]+/', '', trim( $value ) );

        if ( strlen( $value ) < 10 || strlen( $value ) > 500 ) {
            wp_safe_redirect( add_query_arg( 'updated', 'invalid_key', $redirect ) . '#api' );
            exit;
        }

        update_option( 'def_google_api_key', $value, 'no' );
        wp_safe_redirect( add_query_arg( 'updated', 'key_saved', $redirect ) . '#api' );
        exit;
    }

    public function handle_delete_google_api_key() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permissions insuffisantes', 'Erreur', [ 'response' => 403 ] );
        }
        check_admin_referer( DEF_NONCE_ACTION );

        delete_option( 'def_google_api_key' );
        wp_safe_redirect( add_query_arg( 'updated', 'key_deleted', $this->settings_page_url() ) . '#api' );
        exit;
    }

    /* ============================================================
     * Rendu
     * ============================================================ */

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Permissions insuffisantes' );

        $stored_key = (string) get_option( 'def_google_api_key', '' );
        $has_key    = $stored_key !== '';
        $masked     = $has_key ? self::mask_api_key( $stored_key ) : '';
        $country    = get_option( 'def_country_restriction', 'all' );

        $updated  = isset( $_GET['updated'] )         ? sanitize_key( $_GET['updated'] )         : '';
        $settings = isset( $_GET['settings-updated'] ) ? sanitize_key( $_GET['settings-updated'] ) : '';

        $all_widgets = DEF_Elementor_Widget_List::instance()->get_list();
        $widgets     = array_filter( $all_widgets, function ( $w ) { return $w['type'] === 'widget'; } );
        $extensions  = array_filter( $all_widgets, function ( $w ) { return $w['type'] === 'extension'; } );

        $widgets_enabled    = 0;
        foreach ( $widgets    as $w ) { if ( get_option( $w['name'], false ) ) $widgets_enabled++; }
        $extensions_enabled = 0;
        foreach ( $extensions as $w ) { if ( get_option( $w['name'], false ) ) $extensions_enabled++; }

        $countries = [
            'all' => 'Tous les pays',
            'fr'  => 'France',
            'us'  => 'États-Unis',
            'gb'  => 'Royaume-Uni',
            'ca'  => 'Canada',
            'de'  => 'Allemagne',
        ];

        $focus_api = in_array( $updated, [ 'key_saved', 'key_deleted', 'invalid_key' ], true );
        ?>
        <div class="def-wrap">

            <?php if ( $updated === 'key_saved' ) : ?>
                <div class="notice notice-success is-dismissible def-notice"><p>✓ Clé API Google enregistrée.</p></div>
            <?php elseif ( $updated === 'key_deleted' ) : ?>
                <div class="notice notice-success is-dismissible def-notice"><p>✓ Clé API Google supprimée.</p></div>
            <?php elseif ( $updated === 'invalid_key' ) : ?>
                <div class="notice notice-error is-dismissible def-notice"><p>⚠ Clé invalide (10 à 500 caractères, sans espace).</p></div>
            <?php elseif ( $settings === 'true' ) : ?>
                <div class="notice notice-success is-dismissible def-notice"><p>✓ Réglages enregistrés.</p></div>
            <?php endif; ?>

            <!-- Header sombre -->
            <div class="def-header">
                <div class="def-header-left">
                    <div class="def-logo"><span class="dashicons dashicons-forms"></span></div>
                    <div>
                        <h1>DC Elementor Fields</h1>
                        <p>Champs &amp; extensions supplémentaires pour Elementor Forms</p>
                    </div>
                </div>
                <div class="def-header-right">
                    <?php if ( $has_key ) : ?>
                        <span class="def-pill">API Google connectée</span>
                    <?php endif; ?>
                    <span class="def-version-badge">v<?php echo esc_html( DEF_VERSION ); ?></span>
                </div>
            </div>

            <!-- Layout 2 colonnes -->
            <div class="def-layout">
                <div class="def-main">

                    <!-- Onglets -->
                    <nav class="def-tabs" role="tablist">
                        <a href="#api" class="def-tab active" data-tab="api" role="tab" aria-selected="true">
                            <span class="dashicons dashicons-location-alt"></span> API Google
                        </a>
                        <a href="#fields" class="def-tab" data-tab="fields" role="tab" aria-selected="false">
                            <span class="dashicons dashicons-feedback"></span> Champs de formulaire
                        </a>
                        <a href="#extensions" class="def-tab" data-tab="extensions" role="tab" aria-selected="false">
                            <span class="dashicons dashicons-admin-plugins"></span> Extensions
                        </a>
                    </nav>

                    <!-- Panneau API Google -->
                    <div class="def-tab-content active<?php echo $focus_api ? ' def-focus' : ''; ?>" data-panel="api">

                        <!-- scard 1 : clé API (admin-post, jamais rendue en clair) -->
                        <div class="def-scard">
                            <div class="def-scard-header">
                                <div class="def-scard-icon" style="background:#e8f0fe;">
                                    <span class="dashicons dashicons-admin-network" style="color:#1a73e8;"></span>
                                </div>
                                <div>
                                    <h3>Clé API Google Maps</h3>
                                    <p>Maps JavaScript, Places &amp; Geocoding — requise pour le champ City Autocomplete</p>
                                </div>
                            </div>

                            <table class="def-sform-table">
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        <?php if ( $has_key ) : ?>
                                            <div class="def-file-ok">
                                                <span class="dashicons dashicons-yes-alt"></span>
                                                Clé enregistrée
                                                <span class="def-file-valid-badge">Active</span>
                                                <code class="def-key-mask"><?php echo esc_html( $masked ); ?></code>
                                            </div>

                                            <div class="def-inline-actions">
                                                <button type="button" class="def-btn-secondary" data-def-replace-toggle>
                                                    <span class="dashicons dashicons-edit"></span> Remplacer la clé
                                                </button>
                                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" data-def-delete-form>
                                                    <?php wp_nonce_field( DEF_NONCE_ACTION ); ?>
                                                    <input type="hidden" name="action" value="def_delete_google_api_key">
                                                    <button type="submit" class="def-btn-danger">
                                                        <span class="dashicons dashicons-trash"></span> Supprimer
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Formulaire de remplacement (vide par défaut) -->
                                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="def-replace-form" data-def-replace-form autocomplete="off">
                                                <?php wp_nonce_field( DEF_NONCE_ACTION ); ?>
                                                <input type="hidden" name="action" value="def_save_google_api_key">
                                                <input type="password"
                                                       name="api_key"
                                                       class="def-sinput"
                                                       value=""
                                                       placeholder="AIza..."
                                                       autocomplete="off"
                                                       spellcheck="false"
                                                       required>
                                                <p class="def-sdesc">Saisir une nouvelle valeur écrase intégralement la clé existante.</p>
                                                <div class="def-inline-actions" style="margin-top:8px;">
                                                    <button type="submit" class="def-btn-save">
                                                        <span class="dashicons dashicons-saved"></span> Enregistrer la nouvelle clé
                                                    </button>
                                                </div>
                                            </form>

                                        <?php else : ?>
                                            <div class="def-file-missing">
                                                <span class="dashicons dashicons-warning"></span>
                                                Aucune clé configurée
                                                <span class="def-file-invalid-badge">Inactif</span>
                                            </div>

                                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px;" autocomplete="off">
                                                <?php wp_nonce_field( DEF_NONCE_ACTION ); ?>
                                                <input type="hidden" name="action" value="def_save_google_api_key">
                                                <input type="password"
                                                       name="api_key"
                                                       class="def-sinput"
                                                       value=""
                                                       placeholder="AIza..."
                                                       autocomplete="off"
                                                       spellcheck="false"
                                                       required>
                                                <p class="def-sdesc">Format Google Cloud : <code>AIza...</code> — Places API doit être activé.</p>
                                                <div class="def-inline-actions" style="margin-top:8px;">
                                                    <button type="submit" class="def-btn-save">
                                                        <span class="dashicons dashicons-saved"></span> Enregistrer la clé
                                                    </button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>

                            <div class="def-scard-footer">
                                <span class="def-save-note">La valeur complète n'est jamais rendue dans le HTML.</span>
                            </div>
                        </div>

                        <!-- scard 2 : restriction pays (options.php) -->
                        <form method="post" action="options.php">
                            <?php settings_fields( 'def_settings_group' ); ?>
                            <div class="def-scard">
                                <div class="def-scard-header">
                                    <div class="def-scard-icon" style="background:#e6f4ea;">
                                        <span class="dashicons dashicons-admin-site-alt3" style="color:#137333;"></span>
                                    </div>
                                    <div>
                                        <h3>Restriction par pays</h3>
                                        <p>Limite les résultats d'autocomplétion à un pays donné</p>
                                    </div>
                                </div>
                                <table class="def-sform-table">
                                    <tr>
                                        <th><label for="def_country_restriction">Pays</label></th>
                                        <td>
                                            <select name="def_country_restriction" id="def_country_restriction" class="def-sselect">
                                                <?php foreach ( $countries as $code => $name ) : ?>
                                                    <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $country, $code ); ?>>
                                                        <?php echo esc_html( $name ); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <p class="def-sdesc">Choisissez « Tous les pays » pour ne pas appliquer de restriction.</p>
                                        </td>
                                    </tr>
                                </table>
                                <div class="def-scard-footer">
                                    <span class="def-save-note">Appliqué immédiatement côté front.</span>
                                    <button type="submit" class="def-btn-save">
                                        <span class="dashicons dashicons-saved"></span> Enregistrer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Panneau Champs de formulaire -->
                    <div class="def-tab-content" data-panel="fields">
                        <form method="post" action="options.php">
                            <?php settings_fields( 'def_settings_group' ); ?>
                            <div class="def-scard">
                                <div class="def-scard-header">
                                    <div class="def-scard-icon" style="background:#e6f4ea;">
                                        <span class="dashicons dashicons-feedback" style="color:#137333;"></span>
                                    </div>
                                    <div>
                                        <h3>Champs de formulaire</h3>
                                        <p>Activez les types de champ Elementor Forms ajoutés par le plugin</p>
                                    </div>
                                </div>
                                <div class="def-toggle-list">
                                    <?php foreach ( $widgets as $widget ) :
                                        $checked = (bool) get_option( $widget['name'], false ); ?>
                                        <div class="def-toggle-row">
                                            <div class="def-toggle-row-info">
                                                <span class="def-toggle-row-title"><?php echo esc_html( $widget['title'] ); ?></span>
                                                <span class="def-toggle-row-sub"><code><?php echo esc_html( $widget['slug'] ); ?></code></span>
                                            </div>
                                            <label class="def-toggle-label">
                                                <div class="def-toggle-wrap">
                                                    <input type="hidden" name="<?php echo esc_attr( $widget['name'] ); ?>" value="">
                                                    <input type="checkbox"
                                                           id="<?php echo esc_attr( $widget['name'] ); ?>"
                                                           name="<?php echo esc_attr( $widget['name'] ); ?>"
                                                           value="1"
                                                           class="def-toggle-input"
                                                           <?php checked( true, $checked ); ?>>
                                                    <span class="def-toggle-slider"></span>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="def-scard-footer">
                                    <span class="def-save-note"><?php echo (int) $widgets_enabled; ?> / <?php echo count( $widgets ); ?> champs actifs</span>
                                    <button type="submit" class="def-btn-save">
                                        <span class="dashicons dashicons-saved"></span> Enregistrer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Panneau Extensions -->
                    <div class="def-tab-content" data-panel="extensions">
                        <form method="post" action="options.php">
                            <?php settings_fields( 'def_settings_group' ); ?>
                            <div class="def-scard">
                                <div class="def-scard-header">
                                    <div class="def-scard-icon" style="background:#f3e8fd;">
                                        <span class="dashicons dashicons-admin-plugins" style="color:#7c3aed;"></span>
                                    </div>
                                    <div>
                                        <h3>Extensions</h3>
                                        <p>Fonctionnalités complémentaires qui modifient les champs existants</p>
                                    </div>
                                </div>
                                <div class="def-toggle-list">
                                    <?php foreach ( $extensions as $widget ) :
                                        $checked = (bool) get_option( $widget['name'], false ); ?>
                                        <div class="def-toggle-row">
                                            <div class="def-toggle-row-info">
                                                <span class="def-toggle-row-title"><?php echo esc_html( $widget['title'] ); ?></span>
                                                <span class="def-toggle-row-sub"><code><?php echo esc_html( $widget['slug'] ); ?></code></span>
                                            </div>
                                            <label class="def-toggle-label">
                                                <div class="def-toggle-wrap">
                                                    <input type="hidden" name="<?php echo esc_attr( $widget['name'] ); ?>" value="">
                                                    <input type="checkbox"
                                                           id="<?php echo esc_attr( $widget['name'] ); ?>"
                                                           name="<?php echo esc_attr( $widget['name'] ); ?>"
                                                           value="1"
                                                           class="def-toggle-input"
                                                           <?php checked( true, $checked ); ?>>
                                                    <span class="def-toggle-slider"></span>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="def-scard-footer">
                                    <span class="def-save-note"><?php echo (int) $extensions_enabled; ?> / <?php echo count( $extensions ); ?> extensions actives</span>
                                    <button type="submit" class="def-btn-save">
                                        <span class="dashicons dashicons-saved"></span> Enregistrer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Sidebar -->
                <aside class="def-sidebar">
                    <div class="def-scard def-scard-sm">
                        <h3 class="def-sidebar-title">
                            <span class="dashicons dashicons-performance"></span> Statut
                        </h3>

                        <?php if ( $has_key ) : ?>
                            <div class="def-status-box ok">
                                <span class="def-sdot"></span> Google Maps API connectée
                            </div>
                        <?php else : ?>
                            <div class="def-status-box idle">
                                <span class="def-sdot"></span> Google Maps API non configurée
                            </div>
                        <?php endif; ?>

                        <div class="def-status-box <?php echo $widgets_enabled ? 'ok' : 'idle'; ?>">
                            <span class="def-sdot"></span>
                            <?php echo (int) $widgets_enabled; ?> / <?php echo count( $widgets ); ?> champs actifs
                        </div>
                        <div class="def-status-box <?php echo $extensions_enabled ? 'ok' : 'idle'; ?>">
                            <span class="def-sdot"></span>
                            <?php echo (int) $extensions_enabled; ?> / <?php echo count( $extensions ); ?> extensions actives
                        </div>
                    </div>

                    <div class="def-scard def-scard-sm">
                        <h3 class="def-sidebar-title">
                            <span class="dashicons dashicons-editor-help"></span> Configuration Google
                        </h3>
                        <ol class="def-help-list">
                            <li>Ouvrez la <a href="https://console.cloud.google.com/google/maps-apis/credentials" target="_blank" rel="noopener">Google Cloud Console</a>.</li>
                            <li>Activez <strong>Maps JavaScript API</strong>, <strong>Places API</strong> et <strong>Geocoding API</strong>.</li>
                            <li>Créez une clé API et restreignez-la à votre domaine.</li>
                            <li>Collez-la dans l'onglet « API Google ».</li>
                        </ol>
                        <div class="def-help-note">
                            <span class="dashicons dashicons-shield"></span>
                            La clé est stockée avec <code>autoload=no</code> et n'apparaît jamais en clair dans le HTML admin.
                        </div>
                    </div>
                </aside>
            </div>
        </div>
        <?php
    }

    public function def_settings_link( array $links ) {
        $url = $this->settings_page_url();
        $settings_link = '<a href="' . esc_url( $url ) . '">' . __( 'Settings', 'dc-elementor-fields' ) . '</a>';
        $links[] = $settings_link;
        return $links;
    }

}

function run_dc_elementor_fields() {
    $plugin = new Dc_Elementor_Fields();
}
add_action('plugins_loaded', 'run_dc_elementor_fields');
