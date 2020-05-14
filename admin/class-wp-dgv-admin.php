<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_DGV
 * @subpackage WP_DGV/admin
 * @copyright     Darko Gjorgjijoski <info@codeverve.com>
 * @license GPLv2
 */
class WP_DGV_Admin {

	const PAGE_VIMEO = 'dgv-library';
	const PAGE_SETTINGS = 'dgv-settings';

	/**
	 * The vimeo api helper
	 * @var WP_DGV_Api_Helper
	 */
	public $api_helper = null;

	/**
	 * The database helper
	 * @var null
	 */
	public $db_helper = null;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->api_helper  = new WP_DGV_Api_Helper();
		$this->db_helper   = new WP_DGV_Db_Helper();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), filemtime( plugin_dir_path( __FILE__ ) . 'css/admin.css' ), 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// Validate the current screen
		$current_screen           = get_current_screen();
		$is_edit_screen           = isset( $_GET['post'] ) && is_numeric( $_GET['post'] );
		$is_create_screen         = $current_screen->action === 'add' && $current_screen->base === 'post';
		$is_gutenberg_active      = wvv_is_gutenberg_active();
		$is_create_or_edit_screen = $is_create_screen || $is_edit_screen;


		// Get all uploads
		$uploads = null;
		if ( $is_create_or_edit_screen ) {
			$uploads = $this->db_helper->get_uploaded_videos();
		}

		// Sweetalert
		wp_enqueue_script( 'swal', WP_VIMEO_VIDEOS_URL . 'admin/resources/swal.min.js', null, null, true );

		// TUS
		wp_enqueue_script( 'dgv-tus', WP_VIMEO_VIDEOS_URL . 'admin/js/tus.min.js', null, '1.8.0' );

		// Uploader
		wp_enqueue_script( 'dgv-uploader', WP_VIMEO_VIDEOS_URL . 'admin/js/uploader.js', array( 'dgv-tus' ), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/js/uploader.js' ) );

		// Admin
		wp_enqueue_script( $this->plugin_name, WP_VIMEO_VIDEOS_URL . 'admin/js/admin.js', array(
			'jquery',
			'dgv-uploader'
		), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/js/admin.js' ), true );
		wp_localize_script( $this->plugin_name, 'DGV', array(
			'nonce'               => wp_create_nonce( 'dgvsecurity' ),
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'access_token'        => get_option( 'dgv_access_token' ),
			'api_scopes'          => $this->api_helper->scopes,
			'default_privacy'     => apply_filters( 'dgv_default_privacy', 'anybody' ),
			'uploading'           => sprintf( '%s %s', '<img src="' . admin_url( 'images/spinner.gif' ) . '">', __( 'Uploading video. Please wait...', 'wp-vimeo-videos' ) ),
			'sorry'               => __( 'Sorry', 'wp-vimeo-videos' ),
			'upload_invalid_file' => __( 'Please select valid video file.', 'wp-vimeo-videos' ),
			'success'             => __( 'Success', 'wp-vimeo-videos' ),
			'cancel'              => __( 'Cancel', 'wp-vimeo-videos' ),
			'confirm'             => __( 'Confirm', 'wp-vimeo-videos' ),
			'close'               => __( 'Close', 'wp-vimeo-videos' ),
			'correct_errors'      => __('Please correct the following errors', 'wp-vimeo-videos'),
			'uploads'             => $uploads,
		) );


		if ( $is_gutenberg_active && $is_create_or_edit_screen ) {
			wp_enqueue_script( 'wvv-vimeo-upload-block', WP_VIMEO_VIDEOS_URL . 'admin/blocks/upload/script.js', array(
				'wp-blocks',
				'wp-editor',
				'jquery',
				'dgv-uploader'
			), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/blocks/upload/script.js' ) );
			wp_enqueue_style( 'wvv-vimeo-upload-block', WP_VIMEO_VIDEOS_URL . 'admin/blocks/upload/style.css', array(), filemtime( WP_VIMEO_VIDEOS_PATH . 'admin/blocks/upload/style.css' ), 'all' );

			wp_localize_script( 'wvv-vimeo-upload-block', 'DGVUB', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			) );
		}
	}


	/**
	 * Register the admin menus
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		add_media_page(
			__( 'WP Vimeo Library', 'wp-vimeo-videos' ),
			'Vimeo',
			'upload_files',
			self::PAGE_VIMEO, array(
			$this,
			'render_vimeo_page'
		) );
		add_options_page(
			__( 'WP Vimeo Settings', 'wp-vimeo-videos' ),
			'Vimeo',
			'manage_options',
			self::PAGE_SETTINGS, array(
			$this,
			'render_settings_page'
		) );
	}

	/**
	 * Renders the vimeo pages
	 */
	public function render_vimeo_page() {
		echo wvv_get_view( 'admin/partials/library', array(
			'vimeo_helper' => $this->api_helper,
			'db_helper'    => $this->db_helper,
		) );
	}

	/**
	 * Renders the settings page
	 */
	public function render_settings_page() {
		echo wvv_get_view( 'admin/partials/settings', array(
			'vimeo_helper' => $this->api_helper,
			'db_helper'    => $this->db_helper,
		) );
	}

	/**
	 * Add instructions view
	 */
	public function instructions() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Verify dismiss
		$dismiss_key = 'dgv_instructions_dismissed';
		if ( isset( $_GET['wvv_dismiss_instructions'] ) && isset( $_GET['wvv_nonce'] ) ) {
			if(wp_verify_nonce($_GET['wvv_nonce'], 'wvv_instructions_dismiss')) {
				update_option($dismiss_key, 1);
			}
		}
		// Render if not dismissed.
		$instructions_hidden = get_option( $dismiss_key );
		if ( ! $instructions_hidden || empty( $instructions_hidden ) || intval( $instructions_hidden ) !== 1 ) {
			$disallowed = array();
			$page       = isset( $_GET['page'] ) ? $_GET['page'] : null;
			if ( ! in_array( $page, $disallowed ) ) {
				include WP_VIMEO_VIDEOS_PATH . '/admin/partials/instructions.php';
			}
		}
	}

    /**
     * Add a link to the settings page on the plugins.php page.
     *
     * @param  array  $links  List of existing plugin action links.
     *
     * @return array         List of modified plugin action links.
     *
     */
    function plugin_action_links($links)
    {

        $links = array_merge(array(
            '<a href="'.esc_url(admin_url('/options-general.php?page=dgv-settings')).'">'.__('Settings',
                'wp-vimeo-videos').'</a>'
        ), $links);

        return $links;
    }

    /**
     * Add link to the PRO version on the plugins.php page.
     *
     * @param $plugin_meta
     * @param $plugin_file
     * @param $plugin_data
     * @param $status
     *
     * @return mixed
     */
    public function plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status)
    {

        if (WP_VIMEO_VIDEOS_BASENAME === $plugin_file) {
            $plugin_meta[] = '<a class="wvv-color-green" target="_blank" href="'.esc_url(wvv_get_purchase_url()).'"><strong>'.__('Buy PRO Version',
                    'wp-vimeo-videos').'</strong></a>';
        }

        return $plugin_meta;
    }

}
