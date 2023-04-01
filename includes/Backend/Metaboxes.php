<?php

namespace Vimeify\Core\Backend;

use IgniteKit\WP\OptionBuilder\Framework;

use Vimeify\Core\Abstracts\BaseProvider;

class Metaboxes extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		$this->register_upload_profiles();
	}

	/**
	 * Register the upload profiles metaboxes
	 * @return void
	 */
	private function register_upload_profiles() {

		$metabox = array(
			'id'        => 'profile_settings',
			'title'     => __( 'Profile Settings', 'theme-text-domain' ),
			'desc'      => '',
			'pages'     => array( 'dgv-uprofile' ),
			'context'   => 'normal',
			'priority'  => 'high',
			'save_mode' => 'compact',
			'fields'    => array(
				array(
					'label' => __( 'General', 'theme-text-domain' ),
					'id'    => 'general',
					'type'  => 'tab',
				),
				[
					'id'           => 'behavior',
					'label'        => __( 'Default Behavior', 'theme-text-domain' ),
					'desc'         => '',
					'std'          => '',
					'type'         => 'checkbox',
					'section'      => 'general',
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
							'label' => __( 'Enable saving videos locally (Media > Vimeo) that are uploded by using this profile', 'theme-text-domain' ),
							'src'   => '',
						),
						'use_pull_method'     => array(
							'value' => 1,
							'label' => __( 'Prefer "pull" uploads where that is possible', 'theme-text-domain' ),
							'desc'  => __( 'Recommended if your site is accessible on internet (not localhost, password protected, etc). Vimeo will download the video file from your server after upload then the file will be deleted from your server via cron later. This way videos will not be uploaded via PHP after form submission which is sometimes unreliable', 'wp-vimeo-videos-pro' ),
							'src'   => '',
						)
					),
				],
				array(
					'label' => __( 'Privacy', 'theme-text-domain' ),
					'id'    => 'privacy',
					'type'  => 'tab',
				),
				[
					'id'           => 'view_privacy',
					'label'        => __( 'Who can view the videos on vimeo.com', 'wp-vimeo-videos-pro' ),
					'desc'         => __( 'Enable this if you want to prevent certain audiences from viewing your videos.', 'wp-vimeo-videos-pro' ),
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
					'choices'      => $this->get_view_privacy_options(),
				],
				[
					'id'           => 'embed_domains',
					'label'        => __( 'Where the uploaded videos can be embedded (comma separated list of domains)', 'wp-vimeo-videos-pro' ),
					'desc'         => __( 'Enable this if you want to prevent embedding your videos on other domains than those specified here.', 'wp-vimeo-videos-pro' ),
					'std'          => '',
					'type'         => 'text',
					'section'      => 'privacy',
					'rows'         => '',
					'post_type'    => '',
					'taxonomy'     => '',
					'min_max_step' => '',
					'class'        => '',
					'condition'    => '',
					'operator'     => 'and',
				],

				array(
					'label' => __( 'Folders', 'theme-text-domain' ),
					'id'    => 'folders',
					'type'  => 'tab',
				),
				[
					'id'           => 'folder',
					'label'        => __( 'Which Folder will be used for the uploaded videos', 'wp-vimeo-videos-pro' ),
					'desc'         => __( 'Select a folder where the videos uploaded through different areas on your website that use this profile will be stored. Choose "Default" to omit the folders.' ),
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
					'choices'      => $this->get_lazyloaded_options( 'folder', 'folders' ),
				],

				array(
					'label' => __( 'Embed Presets', 'theme-text-domain' ),
					'id'    => 'embed_presets',
					'type'  => 'tab',
				),

				$this->create_embed_presets_settings()

			),
		);

		$framework = new Framework();
		$framework->register_metabox( $metabox );
	}

	/**
	 * Return the lazyloaded folder options
	 *
	 * @param $key
	 * @param $type
	 *
	 * @return array
	 */
	protected function get_lazyloaded_options( $key, $type ) {

		$post_id = is_admin() && ( isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) && ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) ? intval( $_GET['post'] ) : 0;

		if ( ! $post_id ) {
			return [];
		}

		static $settings = null;
		if ( is_null( $settings ) ) {
			$settings = get_post_meta( $post_id, 'profile_settings', true );
		}
		$current_value = isset( $settings[ $key ] ) ? $settings[ $key ] : null;

		$current_name = __( 'Default / None', 'wp-vimeo-videos-pro' );
		if ( ! empty( $current_value ) && ( 'default' != $current_value ) ) {
			switch($type) {
				case 'folders':
					$current_name = $this->plugin->system()->vimeo()->get_folder_name( $current_value );
					break;
				case 'embed_presets':
					$current_name = $this->plugin->system()->vimeo()->get_embed_preset_name( $current_value );
					break;
			}
		}

		return [
			[
				'value' => $current_value,
				'label' => $current_name,
			]
		];

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
	 * Create embed preset settings
	 * @return array
	 */
	protected function create_embed_presets_settings() {

		$label = __( 'Which Embed Preset the uploaded video will use', 'wp-vimeo-videos-pro' );
		$desc  = __( 'Select embed preset for the Vimeo uploads performed in the Front-End. Choose "Default" to omit the embed presets.', 'wp-vimeo-videos-pro' );


		if ( $this->plugin->system()->vimeo()->supports_embed_presets() ) {
			return [
				'id'           => 'embed_preset',
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
				'choices'      => $this->get_lazyloaded_options( 'embed_preset', 'embed_presets' ), // TODO: Add currently selected choices.
			];
		} else {
			return [
				'id'      => 'defalt_preset',
				'label'   => $label,
				'type'    => 'html',
				'section' => 'embed_presets',
				'markup'  => sprintf( '<p><strong>%s</strong>: %s</p>', __( 'Note', 'wp-vimeo-videos-pro' ), __( 'Embed presets are supported on Vimeo Plus or higher plans.', 'wp-vimeo-videos-pro' ) ),
			];
		}
	}

}