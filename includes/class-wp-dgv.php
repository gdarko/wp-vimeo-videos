<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of Video Uploads for Vimeo
 *
 * Video Uploads for Vimeo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Video Uploads for Vimeo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Video Uploads for Vimeo. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV
 *
 * Main class for bootstrapping the plugin
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_DGV_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The basename of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_basename;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version         = WP_VIMEO_VIDEOS_VERSION;
		$this->plugin_name     = 'wp-vimeo-videos';
		$this->plugin_basename = 'wp-vimeo-videos/wp-vimeo-videos.php';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_shared_hooks(); // Note: Must be here.
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Load vimeo library
		 */
		$this->load_vimeo_api_lib();

		/**
		 * Load the composer packages that are required to run this plugin
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/helpers.php';

		/**
		 * The vimeo class
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-vimeo.php';

		/**
		 * The class responsible for settings management
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-settings-base.php';
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-settings-helper.php';

		/**
		 * The class responsible for logging
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-logger.php';

		/**
		 * The class responsible for displaying notices
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-notices-helper.php';

		/**
		 * The class responsible for communicating with the vimeo API
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-api-base.php';

		/**
		 * The class responsible for communicating with the vimeo API
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-api-helper.php';

		/**
		 * The class responsible for communicating with the database and querying data
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-db-base.php';
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-db-helper.php';

		/**
		 * The class responsible for communicating with the news service
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-news-helper.php';

		/**
		 * The class responsible for registering the post types
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-post-types.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-i18n.php';

		/**
		 * The class responsible for creating list table of the videos
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-list-table.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'shared/class-wp-dgv-shared.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'admin/class-wp-dgv-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'public/class-wp-dgv-public.php';

		/**
		 * The class responsible for handling all ajax requests
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-cron.php';

		/**
		 * The class responsible defining the internal hooks
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-internal-hooks.php';

		/**
		 * The class responsible for handling all ajax requests
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-ajax-handler.php';

		/**
		 * The class responsible for logging
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-migrator.php';

		/**
		 * The class responsible for syncing videos locally
		 */
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-sync.php';


		$this->loader = new WP_DGV_Loader();

	}

	/**
	 * Load the vimeo API library
	 * @return void
	 * @since 1.8.1
	 */
	private function load_vimeo_api_lib() {

		$use_latest = apply_filters( 'dgv_use_latest_vimeo_client', true );
		$lib_dir    = WP_VIMEO_VIDEOS_PATH . 'libraries' . DIRECTORY_SEPARATOR;
		$lib_path   = null;

		/**
		 * If latest verison is allowed, attempt to determine it.
		 */
		if ( $use_latest && version_compare( PHP_VERSION, '7.2.5' ) >= 0 ) {
			if ( ! class_exists( "\GuzzleHttp\Client" ) ) {
				$lib_path = 'vimeo-php-guzzle7/vendor/autoload.php';
			} else {
				if ( defined( '\GuzzleHttp\Client::MAJOR_VERSION' ) ) {
					$lib_path = 'vimeo-php-guzzle7/vendor/autoload.php';
				} else {
					$lib_path = 'vimeo-php-guzzle6/vendor/autoload.php';
				}
			}
		}

		/**
		 * Fallback to the legacy version.
		 */
		if ( is_null( $lib_path ) ) {
			$lib_path = 'vimeo-php-legacy/autoload.php';
		}

		require_once $lib_dir . $lib_path;
		require_once WP_VIMEO_VIDEOS_PATH . 'includes/class-wp-dgv-vimeo.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_DGV_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_DGV_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// Init Classes
		$plugin_admin   = new WP_DGV_Admin( $this->get_plugin_name(), $this->get_version() );
		$ajax_handler   = new WP_DGV_Ajax_Handler( $this->get_plugin_name(), $this->get_version() );
		$cron_system    = new WP_DGV_Cron();
		$migrator       = new WP_DGV_Migrator();
		$internal_hooks = new WP_DGV_Internal_Hooks();
		$post_types     = new WP_DGV_Post_Types();

		// Init Migration
		$this->loader->add_action( 'init', $migrator, 'init' );

		// Init Dashboard
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_admin_menu' );
		$this->loader->add_action( 'in_admin_header', $plugin_admin, 'do_admin_notices', 50 );
		$this->loader->add_filter( 'plugin_action_links_' . WP_VIMEO_VIDEOS_BASENAME, $plugin_admin, 'plugin_action_links', 100, 1 );
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 100, 4 );

		// Init Post Types
		$this->loader->add_action( 'init', $post_types, 'init' );

		// Int Cron tasks
		$cron_system->init( $this );

		// Init Ajax endpoints
		$this->loader->add_action( 'wp_ajax_dgv_handle_upload', $ajax_handler, 'handle_upload' );
		$this->loader->add_action( 'wp_ajax_dgv_handle_settings', $ajax_handler, 'handle_settings' );
		$this->loader->add_action( 'wp_ajax_dgv_store_upload', $ajax_handler, 'store_upload' );
		$this->loader->add_action( 'wp_ajax_dgv_user_search', $ajax_handler, 'handle_user_search' );
		$this->loader->add_action( 'wp_ajax_dgv_get_uploads', $ajax_handler, 'get_uploads' );

		// Hooks
		$this->loader->add_action( 'dgv_backend_after_upload', $internal_hooks, 'backend_after_upload', 5 );
	}


	/**
	 * Register all of the hooks related to both admin area and fron area.
	 * @since 1.8.1
	 * @return void
	 */
	private function define_shared_hooks() {
		$plugin_shared = new WP_DGV_Shared();
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_shared, 'register_scripts', 0 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_shared, 'register_scripts', 0 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_shared, 'enqueue_scripts', 1000 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_shared, 'enqueue_scripts', 1000 );
		$this->loader->add_action( 'before_wp_tiny_mce', $plugin_shared, 'tinymce_globals' );
		$this->loader->add_action( 'after_setup_theme', $plugin_shared, 'tinymce_styles' );
		$this->loader->add_filter( 'mce_buttons', $plugin_shared, 'tinymce_vimeo_button' );
		$this->loader->add_filter( 'mce_external_plugins', $plugin_shared, 'tinymce_vimeo_plugin' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WP_DGV_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'the_content', $plugin_public, 'video_contents' );

		add_shortcode( 'vimeo_video', array( $plugin_public, 'shortcode_video' ) ); // DEPRECATED.
		add_shortcode( 'dgv_vimeo_video', array( $plugin_public, 'shortcode_video' ) );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    WP_DGV_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
