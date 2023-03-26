<?php

namespace Vimeify\Core\Backend;

use Vimeify\Core\Abstracts\BaseProvider;

class Options extends BaseProvider {

	/**
	 * The list of array
	 * @var array
	 */
	protected $args;

	/**
	 * Constructor
	 */
	public function register() {

		$this->configure();

		$required_sections = [
			[
				'id'    => 'overview',
				'title' => __( 'Overview', 'wp-vimeo-videos-pro' ),
			],
			[
				'id'    => 'api_credentials',
				'title' => __( 'API Credentials', 'wp-vimeo-videos-pro' ),
			],
		];

		$required_settings = [
			/**
			 * API Connection
			 */
			[
				'id'      => 'connection',
				'label'   => __( 'Connection', 'wp-vimeo-videos-pro' ),
				'type'    => 'html',
				'section' => 'overview',
				'markup'  => [ $this, 'create_overview_connection' ],
			],
			[
				'id'      => 'environment',
				'label'   => __( 'Environment', 'wp-vimeo-videos-pro' ),
				'type'    => 'html',
				'section' => 'overview',
				'markup'  => [ $this, 'create_overview_environment' ],
			],
			[
				'id'      => 'issues',
				'label'   => __( 'Troubleshooting', 'wp-vimeo-videos-pro' ),
				'type'    => 'html',
				'section' => 'overview',
				'markup'  => [ $this, 'create_overview_issues' ],
			],
			[
				'id'           => 'client_id',
				'label'        => __( 'Client ID', 'wp-vimeo-videos-pro' ),
				'desc'         => '',
				'std'          => '',
				'type'         => 'text',
				'section'      => 'api_credentials',
				'rows'         => '',
				'post_type'    => '',
				'taxonomy'     => '',
				'min_max_step' => '',
				'class'        => '',
				'condition'    => '',
				'operator'     => 'and',
				'group'        => true,
			],
			[
				'id'           => 'client_secret',
				'label'        => __( 'Client Secret', 'wp-vimeo-videos-pro' ),
				'desc'         => '',
				'std'          => '',
				'type'         => 'text',
				'section'      => 'api_credentials',
				'rows'         => '',
				'post_type'    => '',
				'taxonomy'     => '',
				'min_max_step' => '',
				'class'        => '',
				'condition'    => '',
				'operator'     => 'and',
				'group'        => true,
			],
			[
				'id'           => 'access_token',
				'label'        => __( 'Access Token', 'wp-vimeo-videos-pro' ),
				'desc'         => '',
				'std'          => '',
				'type'         => 'text',
				'section'      => 'api_credentials',
				'rows'         => '',
				'post_type'    => '',
				'taxonomy'     => '',
				'min_max_step' => '',
				'class'        => '',
				'condition'    => '',
				'operator'     => 'and',
				'group'        => true,
			],

		];


		if ( $this->plugin->system()->vimeo()->is_connected ) {

			require_once( ABSPATH . 'wp-includes/pluggable.php' );

			$other_settings = [

				/**
				 * Privacy Settings
				 */
				[
					'id'           => 'view_privacy_admin',
					'label'        => __( 'Who can view the videos uploaded from the Admin Dashboard on vimeo.com?', 'wp-vimeo-videos-pro' ),
					'desc'         => '',
					'std'          => '',
					'type'         => 'select',
					'section'      => 'privacy',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => $this->get_view_privacy_options(),
				],
				[
					'id'           => 'view_privacy_frontend',
					'label'        => __( 'Who can view the videos uploaded from the Front-end on vimeo.com?', 'wp-vimeo-videos-pro' ),
					'desc'         => '',
					'std'          => '',
					'type'         => 'select',
					'section'      => 'privacy',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => $this->get_view_privacy_options(),
				],
				[
					'id'           => 'embed_domains',
					'label'        => __( 'Where the uploaded videos can be embedded? (comma separated list of domains)', 'wp-vimeo-videos-pro' ),
					'desc'         => __( 'Only enable this if you want to prevent embedding your videos on other domains than those specified in this setting.', 'wp-vimeo-videos-pro' ),
					'std'          => '',
					'type'         => 'text',
					'section'      => 'privacy',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'group'        => true,
					'operator'     => 'and',
				],

				/**
				 * Folders Settings
				 */
				[
					'id'           => 'folder_admin',
					'label'        => __( 'Admin default uploads folder', 'wp-vimeo-videos-pro' ),
					'desc'         => __( 'Select a folder for the Vimeo uploads performed in the WordPress admin. Choose "Default" to omit the folders.' ),
					'std'          => '',
					'type'         => 'select',
					'section'      => 'folders',
					'ajax'         => [ 'endpoint' => admin_url( 'admin-ajax.php' ), 'action' => 'dgv_folder_search', 'nonce' => \wp_create_nonce( 'dgvsecurity' ) ],
					'placeholder'  => __( 'Select folder...', 'wp-vimeo-videos-pro' ),
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => $this->get_lazyloaded_folder_options( 'admin' ),
				],
				[
					'id'           => 'folder_frontend',
					'label'        => __( 'Front-end default uploads folder', 'wp-vimeo-videos-pro' ),
					'desc'         => __( 'Select a folder for the Vimeo uploads performed in the WordPress admin. Choose "Default" to omit the folders.' ),
					'std'          => '',
					'type'         => 'select',
					'section'      => 'folders',
					'ajax'         => [ 'endpoint' => admin_url( 'admin-ajax.php' ), 'action' => 'dgv_folder_search', 'nonce' => \wp_create_nonce( 'dgvsecurity' ) ],
					'placeholder'  => __( 'Select folder...', 'wp-vimeo-videos-pro' ),
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => $this->get_lazyloaded_folder_options( 'frontend' ),
				],

				/**
				 * Embed Preset Settings
				 */
				$this->create_embed_presets_settings( 'admin' ),
				$this->create_embed_presets_settings( 'frontend' ),

				// Front-end Settngs

				[
					'id'           => 'behavior',
					'label'        => __( 'Front-end Behavior', 'theme-text-domain' ),
					'desc'         => '',
					'std'          => '',
					'type'         => 'checkbox',
					'section'      => 'frontend',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => array(
						'store_in_library'    => array(
							'value' => 1,
							'label' => __( 'Enable saving videos locally (Media > Vimeo) that are uploded from the frontend', 'theme-text-domain' ),
							'src'   => '',
						),
						'enable_single_pages' => array(
							'value' => 1,
							'label' => __( 'Enable single video pages for the uploaded videos', 'theme-text-domain' ),
							'src'   => '',
						),
						'use_pull_method'     => array(
							'value' => 1,
							'label' => __( 'Enable "pull" uploads for front-end forms', 'theme-text-domain' ),
							'desc'  => __( 'Recommended if your site is accessible on internet (not localhost, password protected, etc). Vimeo will download the video file from your server after upload then the file will be deleted from your server via cron later. This way videos will not be uploaded via PHP after form submission which is sometimes unreliable', 'wp-vimeo-videos-pro' ),
							'src'   => '',
						)
					),
				],

				//
				// Admin Settngs
				[
					'id'           => 'gutenberg',
					'label'        => __( 'Gutenberg Block', 'theme-text-domain' ),
					'desc'         => '',
					'std'          => '',
					'type'         => 'checkbox',
					'section'      => 'admin',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => array(
						'enable_privacy_option'    => array(
							'value' => 1,
							'label' => __( 'Enable video privacy option in Gutenberg upload modal', 'theme-text-domain' ),
							'src'   => '',
						),
						'enable_account_search'    => array(
							'value' => 1,
							'label' => __( 'Enable Vimeo account search option in TinyMCE upload modal', 'theme-text-domain' ),
							'src'   => '',
						),
						'enable_local_search'      => array(
							'value' => 1,
							'label' => __( 'Enable local library search option in the Gutenberg upload modal', 'theme-text-domain' ),
							'src'   => '',
						),
						'show_author_uploads_only' => array(
							'value' => 1,
							'label' => __( 'Only show videos uploaded by the current user in the Video search option', 'theme-text-domain' ),
							'src'   => '',
						)
					),
				],

				[
					'id'           => 'tinymce',
					'label'        => __( 'TinyMCE Modal', 'theme-text-domain' ),
					'desc'         => '',
					'std'          => '',
					'type'         => 'checkbox',
					'section'      => 'admin',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => array(
						'enable_privacy_option'    => array(
							'value' => 1,
							'label' => __( 'Enable video privacy option in TinyMCE upload modal', 'theme-text-domain' ),
							'src'   => '',
						),
						'enable_account_search'    => array(
							'value' => 1,
							'label' => __( 'Enable Vimeo account search option in TinyMCE upload modal', 'theme-text-domain' ),
							'src'   => '',
						),
						'enable_local_search'      => array(
							'value' => 1,
							'label' => __( 'Enable local library search option in TinyMCE upload modal', 'theme-text-domain' ),
							'src'   => '',
						),
						'show_author_uploads_only' => array(
							'value' => 1,
							'label' => __( 'Only show videos uploaded by the current user in the Video search option', 'theme-text-domain' ),
							'src'   => '',
						)
					),
				],

				[
					'id'           => 'media_attachments',
					'label'        => __( 'Media Upload Modal', 'theme-text-domain' ),
					'desc'         => '',
					'std'          => '',
					'type'         => 'checkbox',
					'section'      => 'admin',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => array(
						'enable_privacy_option' => array(
							'value' => 1,
							'label' => __( 'Enable video privacy option in other Media upload modals', 'theme-text-domain' ),
							'src'   => '',
						)
					),
				],

				[
					'id'           => 'video_management',
					'label'        => __( 'Video Management Page', 'theme-text-domain' ),
					'desc'         => __( 'Select which video elements to be enabled for editing videos.', 'wp-vimeo-videos-pro' ),
					'std'          => '',
					'type'         => 'checkbox',
					'section'      => 'admin',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => array(
						'enable_embed_presets' => array(
							'value' => 1,
							'label' => __( 'Enable Embed Presets panel in the single video management page', 'theme-text-domain' ),
							'src'   => '',
						),
						'enable_embed_privacy' => array(
							'value' => 1,
							'label' => __( 'Enable Embed Privacy panel in the single video management page', 'theme-text-domain' ),
							'src'   => '',
						),
						'enable_folders'       => array(
							'value' => 1,
							'label' => __( 'Enable Folders panel in the single video management page', 'theme-text-domain' ),
							'src'   => '',
						),
					),
				],

				[
					'id'           => 'videos_list_table',
					'label'        => __( 'Videos list table', 'theme-text-domain' ),
					'desc'         => '',
					'std'          => '',
					'type'         => 'checkbox',
					'section'      => 'admin',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => array(
						'show_author_uploads_only' => array(
							'value' => 1,
							'label' => __( 'Only show the Videos uploaded by the current user on the Vimeo list table', 'theme-text-domain' ),
							'src'   => '',
						)
					),
				],

				[
					'id'           => 'videos_thumbnails',
					'label'        => __( 'Thumbnails support', 'theme-text-domain' ),
					'desc'         => '',
					'std'          => '',
					'type'         => 'checkbox',
					'section'      => 'admin',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
					'group'        => true,
					'choices'      => array(
						'enable_thumbnails' => array(
							'value' => 1,
							'label' => __( 'Enable experimental support for thumbnails. (Note: May result in increased api calls usage)', 'theme-text-domain' ),
							'src'   => '',
						)
					),
				],

			];
			$other_sections = [
				[
					'id'    => 'admin',
					'title' => __( 'Admin Interface', 'wp-vimeo-videos-pro' ),
				],
				[
					'id'    => 'frontend',
					'title' => __( 'Frontend Interface', 'wp-vimeo-videos-pro' ),
				],
				[
					'id'    => 'folders',
					'title' => __( 'Video Folders', 'wp-vimeo-videos-pro' ),
				],
				[
					'id'    => 'embed_presets',
					'title' => __( 'Embed Presets', 'wp-vimeo-videos-pro' ),
				],
				[
					'id'    => 'privacy',
					'title' => __( 'Privacy Settings', 'wp-vimeo-videos-pro' ),
				]
			];
		} else {
			$other_settings = [];
			$other_sections = [];
		}

		$sections = array_merge( $required_sections, $other_sections );
		$settings = array_merge( $required_settings, $other_settings );


		$sections = apply_filters( 'dgv_settings_sections', $sections );
		$settings = apply_filters( 'dgv_settings_fields', $settings );


		$this->register_options( $sections, $settings );
	}

	/**
	 * Configure the Options Builder
	 * @return void
	 */
	protected function configure() {
		add_filter( 'opb_header_logo_url', function ( $text, $page_id ) {
			if ( $this->plugin->settings_key() === $page_id ) {
				$text = $this->plugin->commercial_url();
			}

			return $text;
		}, 10, 2 );
		add_filter( 'opb_header_version_text', function ( $text, $page_id ) {
			if ( $this->plugin->settings_key()  === $page_id ) {
				$text = __( 'Vimeo Settings', 'wp-vimeo-videos-pro' );
			}

			return $text;
		}, 10, 2 );
		add_filter( 'opb_header_logo_icon', function ( $icon, $page_id ) {
			if ( $this->plugin->settings_key()  === $page_id ) {
				$icon = 'dashicons dashicons-video-alt2';
			}

			return $icon;
		}, 10, 2 );
	}


	/**
	 * Create embed preset settings
	 * @return array
	 */
	protected function create_embed_presets_settings( $type = 'admin' ) {

		switch ( $type ) {
			case 'admin':
				$key   = 'preset_admin';
				$label = __( 'Default Admin Embed preset', 'wp-vimeo-videos-pro' );
				$desc  = __( 'Select embed preset for the Vimeo uploads performed in the WordPress admin. Choose "Default" to omit the embed presets.', 'wp-vimeo-videos-pro' );
				break;
			case 'frontend':
				$key   = 'preset_frontend';
				$label = __( 'Default Frontend Embed preset', 'wp-vimeo-videos-pro' );
				$desc  = __( 'Select embed preset for the Vimeo uploads performed in the Front-End. Choose "Default" to omit the embed presets.', 'wp-vimeo-videos-pro' );
				break;
			default:
				return [];
		}

		if ( $this->plugin->system()->vimeo()->supports_embed_presets() ) {
			return [
				'id'           => $key,
				'label'        => $label,
				'desc'         => $desc,
				'std'          => '',
				'type'         => 'select',
				'section'      => 'embed_presets',
				'ajax'         => [ 'endpoint' => admin_url( 'admin-ajax.php' ), 'action' => 'dgv_embed_preset_search', 'nonce' => \wp_create_nonce( 'dgvsecurity' ) ],
				'placeholder'  => __( 'Select preset...', 'wp-vimeo-videos-pro' ),
				'rows'         => '',
				'post_type'    => '',
				'taxonomy'     => '',
				'min_max_step' => '',
				'class'        => '',
				'condition'    => '',
				'operator'     => 'and',
				'group'        => true,
				'choices'      => $this->get_lazyloaded_embed_preset_options( $type ), // TODO: Add currently selected choices.
			];
		} else {
			return [
				'id'      => $key,
				'label'   => $label,
				'type'    => 'html',
				'section' => 'embed_presets',
				'markup'  => sprintf( '<p><strong>%s</strong>: %s</p>', __( 'Note', 'wp-vimeo-videos-pro' ), __( 'Embed presets are supported on Vimeo PRO or higher plans.', 'wp-vimeo-videos-pro' ) ),
			];
		}
	}


	/**
	 * Create the overview
	 * @return false|string
	 */
	public function create_overview_connection() {

		return '<table class="dgv-status-wrapper">' . $this->plugin->system()->views()->get_view( 'admin/partials/status-api', array(
				'plugin' => $this->plugin
			) ) . '</table>';
	}

	/**
	 * Create the overview
	 * @return false|string
	 */
	public function create_overview_environment() {

		return '<table class="dgv-status-wrapper">' .  $this->plugin->system()->views()->get_view( 'admin/partials/status-env', array(
				'plugin' => $this->plugin
			) ) . '</table>';
	}

	/**
	 * Create the overview
	 * @return false|string
	 */
	public function create_overview_issues() {

		return '<table class="dgv-status-wrapper">' .  $this->plugin->system()->views()->get_view( 'admin/partials/status-issues', array(
				'plugin' => $this->plugin
			) ) . '</table><style>.dgv-status-wrapper {text-align:left;}</style>';
	}

	/**
	 * Transfor the option array
	 * @return array
	 */
	protected function get_view_privacy_options() {

		$option = [];

		$view_privacy_options = $this->plugin->system()->vimeo()->get_view_privacy_options();

		foreach ( $view_privacy_options as $view_privacy_option_key => $view_privacy_option ) {

			$option[] = [
				'value'    => $view_privacy_option_key,
				'label'    => $view_privacy_option['name'],
				'disabled' => isset( $view_privacy_option['available'] ) ? ! (bool) $view_privacy_option['available'] : false,
				'src'      => '',
			];
		}

		return $option;
	}

	/**
	 * Return the lazyloaded folder options
	 *
	 * @param $type
	 *
	 * @return array
	 */
	protected function get_lazyloaded_folder_options( $type ) {

		$current_value = $this->plugin->system()->settings()->get( 'folders.folder_' . $type, '' );

		$current_name = ! empty( $current_value ) && ( 'default' != $current_value ) ? $this->plugin->system()->vimeo()->get_folder_name( $current_value ) : __( 'Default (no folder)', 'wp-vimeo-videos-pro' );

		$choices = [
			[
				'value' => $current_value,
				'label' => $current_name,
			]
		];

		return $choices;

	}

	/**
	 * Return the lazyloaded folder options
	 *
	 * @param $type
	 *
	 * @return array
	 */
	protected function get_lazyloaded_embed_preset_options( $type ) {

		$current_value = $this->plugin->system()->settings()->get( 'embed_presets.preset_' . $type, '' );

		$current_name = ! empty( $current_value ) && ( 'default' != $current_value ) ? $this->plugin->system()->vimeo()->get_embed_preset_name( $current_value ) : __( 'Default (no preset)', 'wp-vimeo-videos-pro' );

		$choices = [
			[
				'value' => $current_value,
				'label' => $current_name,
			]
		];

		return $choices;

	}

	/**
	 * Registers the options
	 *
	 * @param $sections
	 * @param $settings
	 *
	 * @return void
	 */
	protected function register_options( $sections, $settings ) {
		$this->args = [
			'id'    => $this->plugin->settings_key(),
			'pages' => [
				[
					'id'              => $this->plugin->settings_key(),
					'parent_slug'     => 'options-general.php',
					'page_title'      => __( 'Vimeo v2', 'wp-vimeo-videos-pro' ),
					'menu_title'      => __( 'Vimeo v2', 'wp-vimeo-videos-pro' ),
					'capability'      => 'manage_options',
					'menu_slug'       => $this->plugin->settings_key(),
					'icon_url'        => null,
					'position'        => null,
					'updated_message' => __( 'Options updated!', 'wp-vimeo-videos-pro' ),
					'reset_message'   => __( 'Options reset!', 'wp-vimeo-videos-pro' ),
					'button_text'     => __( 'Save changes', 'wp-vimeo-videos-pro' ),
					'show_buttons'    => true,
					'show_subheader'  => false,
					'screen_icon'     => 'options-general',
					'sections'        => $sections,
					'settings'        => $settings
				]
			]
		];

		$this->args = apply_filters( 'dgv_settings_args', $this->args );

		$framework = new \IgniteKit\WP\OptionBuilder\Framework();
		$framework->register_settings( $this->args );
	}


}