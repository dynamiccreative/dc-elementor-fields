<?php
/**
 * GitHub Updater for WordPress Plugin
 *
 * Adds support for updating the plugin from a GitHub repository
 * via the WordPress admin interface.
 */

/**
 * Class MyPlugin_GitHub_Updater
 */
class Fields_GitHub_Updater {

    /**
     * GitHub repository information
     *
     * @var array
     */
    private $config = [
        'slug'          => 'dc-elementor-fields/dc-elementor-fields.php',
        'repo'          => 'dc-elementor-fields',
        'owner'         => 'dynamiccreative', 
        'github_url'    => 'https://github.com/dynamiccreative/dc-elementor-fields',
        'zip_url'       => 'https://github.com/dynamiccreative/dc-elementor-fields/archive/refs/tags/{tag}.zip',
        'access_token'  => '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',
        'icon_url'      => 'https://raw.githubusercontent.com/dynamiccreative/dc-scroll-top/main/assets/img/icon-256x256.png',
        'banner_url'      => 'https://raw.githubusercontent.com/dynamiccreative/dc-scroll-top/main/assets/img/banner-1544x500.png',
    ];

    /**
     * Current plugin data
     *
     * @var array
     */
    private $plugin_data;

    /**
     * Constructor
     */
    public function __construct() {
        // Hook into WordPress to check for updates
        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_for_updates' ] );
        add_filter( 'plugins_api', [ $this, 'plugin_info' ], 9999, 3 );
        add_filter( 'upgrader_post_install', [ $this, 'post_install' ], 10, 3 );
        add_filter( 'plugin_row_meta', [ $this, 'fields_row_meta' ], 10, 2 );
    }

    /**
     * Get plugin data
     *
     * @return array
     */
    private function get_my_plugin_data() {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return get_plugin_data( WP_PLUGIN_DIR . '/' . $this->config['slug'] );
    }

    /**
     * Check for plugin updates
     *
     * @param object $transient
     * @return object
     */
    public function check_for_updates( $transient ) {
        //error_log('update start');
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $this->plugin_data = $this->get_my_plugin_data();
        $current_version = $this->plugin_data['Version'];
        $remote_version = $this->get_latest_version();

        if ( $remote_version && version_compare( $current_version, $remote_version, '<' ) ) {
            $obj = new stdClass();
            $obj->slug = dirname( $this->config['slug'] );
            $obj->plugin = $this->config['slug'];
            $obj->new_version = $remote_version;
            $obj->url = $this->config['github_url'];
            $obj->package = str_replace( '{tag}', $remote_version, $this->config['zip_url'] );
            $obj->icons = array(
                    'default' => $this->config['icon_url']
                );
            $transient->response[ $this->config['slug'] ] = $obj;
        }

        return $transient;
    }

    /**
     * Get plugin information for the update screen
     *
     * @param false|object|array $result
     * @param string $action
     * @param object $args
     * @return object|array|false
     */
    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $result;
        }

        if ( $args->slug !== dirname( $this->config['slug'] ) ) {
            return $result;
        }

        $this->plugin_data = $this->get_my_plugin_data();
        $remote_version = $this->get_latest_version();

        $info = new stdClass();
        $info->name = $this->plugin_data['Name'];
        $info->slug = dirname( $this->config['slug'] );
        $info->version = $remote_version;
        $info->author = $this->plugin_data['Author'];
        $info->homepage = $this->config['github_url'];
        $info->requires = '6.0'; // Adjust as needed
        $info->tested = '6.7.2';   // Adjust as needed
        $info->downloaded = 0;
        $info->last_updated = gmdate( 'Y-m-d' );
        $info->sections = [
            'description' => $this->plugin_data['Description'],
            'changelog'   => $this->get_changelog(), //$changelog['body'].'<br>'.
        ];
        $info->download_link = str_replace( '{tag}', $remote_version, $this->config['zip_url'] );

        // Add plugin icon
        if ( ! empty( $this->config['icon_url'] ) ) {
            $info->icons = [
                '1x' => $this->config['icon_url'],
                '2x' => $this->config['icon_url'], // Use the same image for 2x if no higher resolution is available
            ];
        }

        // Add plugin banners
        if ( ! empty( $this->config['banner_url'] ) ) {
            $info->banners = [
                'low'  => $this->config['banner_url'],
                'high' => ! empty( $this->config['banner_url_high'] ) ? $this->config['banner_url_high'] : $this->config['banner_url'],
            ];
        }

        return $info;
    }

    /**
     * Post-install cleanup
     *
     * @param array $response
     * @param array $hook_extra
     * @param array $result
     * @return array
     */
    public function post_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;

        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname( $this->config['slug'] );
        $wp_filesystem->move( $result['destination'], $plugin_folder );
        $result['destination'] = $plugin_folder;

        return $result;
    }

    /**
     * Get the latest version from GitHub
     *
     * @return string|false
     */
    private function get_latest_version() {
        $url = "https://api.github.com/repos/{$this->config['owner']}/{$this->config['repo']}/releases/latest";
        $headers = [];

        if ( ! empty( $this->config['access_token'] ) ) {
            $headers['Authorization'] = 'token ' . $this->config['access_token'];
        }

        $response = wp_remote_get( $url, [
            'headers' => $headers,
            'timeout' => 10,
        ] );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        //return $data;
        return ! empty( $data['tag_name'] ) ? ltrim( $data['tag_name'], 'v' ) : false;
    }

    /**
     * Get changelog (optional, can be customized)
     *
     * @return string
     */
    private function get_changelog() {
        // You can fetch the changelog from GitHub or hardcode it
        return 'See the full changelog on <a href="' . esc_url( $this->config['github_url'] ) . '/releases">GitHub</a>.';
    }

    /*
     * Ajoute une icone à droite da la version dans la vue liste
     */
    public function fields_row_meta($links, $file) {
        if ($this->config['slug'] === $file) {
            $links[] = '<img src="' . $this->config['icon_url'] . '" alt="Icon" style="width:16px;height:16px;vertical-align:middle;" />';
        }
        return $links;
    }
}

// Instantiate the updater
if ( is_admin() ) {
    new Fields_GitHub_Updater();
}
?>