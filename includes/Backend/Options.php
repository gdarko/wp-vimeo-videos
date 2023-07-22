<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://ideologix.com)
 *
 * This file is part of "Vimeify - Video Uploads for Vimeo"
 *
 * Vimeify - Video Uploads for Vimeo is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Video Uploads for Vimeo is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with "Vimeify - Video Uploads for Vimeo". If not, see <https://www.gnu.org/licenses/>.
 *
 * ---
 *
 * Author Note: This code was written by Darko Gjorgjijoski <dg@darkog.com>
 * If you have any questions find the contact details in the root plugin file.
 *
 **********************************************************************/

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

		$other_sections = [];
		$other_settings = [];

		if ( $this->plugin->system()->vimeo()->is_connected ) {

			require_once( ABSPATH . 'wp-includes/pluggable.php' );

			$other_sections = [
				[
					'id'    => 'admin',
					'title' => __( 'Admin Settings', 'wp-vimeo-videos-pro' ),
				],
				[
					'id'    => 'frontend',
					'title' => __( 'Frontend Settings', 'wp-vimeo-videos-pro' ),
				],
				[
					'id'    => 'upload_profiles',
					'title' => __( 'Upload Profiles', 'wp-vimeo-videos-pro' ),
				],
			];

			$other_settings = [
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
						'enable_single_pages' => array(
							'value' => 1,
							'label' => __( 'Enable single video pages for the uploaded videos', 'theme-text-domain' ),
							'src'   => '',
						),
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

			/**
			 * Add upload profiles
			 */
			foreach ( $this->get_upload_profiles() as $profile ) {
				if ( ! isset( $profile['key'] ) || ! isset( $profile['title'] ) || ! isset( $profile['desc'] ) ) {
					continue;
				}
				array_push( $other_settings, $this->create_upload_profile_option(
					$profile['key'],
					$profile['title'],
					$profile['desc'],
				) );
			}
		}

		/**
		 * Allow filter to modify them
		 */
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
			if ( $this->plugin->settings_key() === $page_id ) {
				$text = __( 'Vimeo Settings', 'wp-vimeo-videos-pro' );
			}

			return $text;
		}, 10, 2 );
		add_filter( 'opb_header_logo_icon', function ( $icon, $page_id ) {
			if ( $this->plugin->settings_key() === $page_id ) {
				$icon = 'dashicons dashicons-video-alt2';
			}

			return $icon;
		}, 10, 2 );
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

		return '<table class="dgv-status-wrapper">' . $this->plugin->system()->views()->get_view( 'admin/partials/status-env', array(
				'plugin' => $this->plugin
			) ) . '</table>';
	}

	/**
	 * Create the overview
	 * @return false|string
	 */
	public function create_overview_issues() {

		return '<table class="dgv-status-wrapper">' . $this->plugin->system()->views()->get_view( 'admin/partials/status-issues', array(
				'plugin' => $this->plugin
			) ) . '</table><style>.dgv-status-wrapper {text-align:left;}</style>';
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
					'parent_slug'     => 'vimeify',
					'page_title'      => __( 'Settings', 'wp-vimeo-videos-pro' ),
					'menu_title'      => __( 'Settings', 'wp-vimeo-videos-pro' ),
					'capability'      => 'manage_options',
					'menu_slug'       => 'dgv-settings',
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

	/**
	 * The default lazyloaded options
	 *
	 * @param $option
	 * @param $section
	 *
	 * @return array[]
	 */
	public function get_lazyloaded_options( $option, $section ) {
		$current_value = $this->plugin->system()->settings()->get( sprintf( '%s.%s', $section, $option ), '' );
		$current_name = ! empty( $current_value ) && ( 'default' != $current_value ) ? get_the_title( $current_value ) : __( 'Default', 'wp-vimeo-videos-pro' );
		return [
			[
				'value' => $current_value,
				'label' => $current_name,
			]
		];

	}


	public function create_upload_profile_option( $id, $title, $description ) {
		return [
			'id'           => $id,
			'label'        => $title,
			'desc'         => $description,
			'std'          => '',
			'type'         => 'select',
			'section'      => 'upload_profiles',
			'ajax'         => [
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'action'   => 'dgv_upload_profile_search',
				'nonce'    => \wp_create_nonce( 'dgvsecurity' )
			],
			'placeholder'  => __( 'Select profile...', 'wp-vimeo-videos-pro' ),
			'rows'         => '',
			'post_type'    => '',
			'taxonomy'     => '',
			'min_max_step' => '',
			'class'        => '',
			'condition'    => '',
			'operator'     => 'and',
			'group'        => true,
			'choices'      => $this->get_lazyloaded_options( $id, 'upload_profiles' ),
		];
	}

	/**
	 * Return upload profiles
	 * @return mixed|null
	 */
	public function get_upload_profiles() {
		return apply_filters( 'dgv_upload_profiles', [
			[
				'key'   => 'default',
				'title' => __( 'Default Profile', 'wp-vimeo-videos-pro' ),
				'desc'  => __( 'Select the profile that will be used for uploads made in other ways than the ones listed below, eg. PHP API, etc.', 'wp-vimeo-videos-pro' )
			],
			[
				'key'   => 'admin_gutenberg',
				'title' => __( 'Gutenberg Block Editor', 'wp-vimeo-videos-pro' ),
				'desc'  => __( 'Select the profile that will be used for uploads made through the Gutenberg (Block Editor) profile in the site admin/backend.', 'wp-vimeo-videos-pro' )
			],
			[
				'key'   => 'admin_classic',
				'title' => __( 'Classic Editor', 'wp-vimeo-videos-pro' ),
				'desc'  => __( 'Select the profile that will be used for uploads made through the TinyMCE (Classic Editor) profile in the site admin/backend.', 'wp-vimeo-videos-pro' )
			],
			[
				'key'   => 'admin_other',
				'title' => __( 'Other Backend Forms', 'wp-vimeo-videos-pro' ),
				'desc'  => __( 'Select the profile that will be used across different areas in the admin side, except those areas that you have defined settings for below.', 'wp-vimeo-videos-pro' )
			]
		] );
	}


}