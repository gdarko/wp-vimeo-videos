<?php

namespace Vimeify\Core\Shared\Blocks;

use Vimeify\Core\Abstracts\BaseBlock;
use Vimeify\Core\Abstracts\Interfaces\CacheInterface;
use Vimeify\Core\Frontend\Views\Video as VideoView;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;

class Video extends BaseBlock {

	/**
	 * Registers block editor assets
	 * @return void
	 */
	public function register_block() {

		$block_path = $this->plugin->path() . 'blocks/dist/video/';
		if ( ! file_exists( $block_path . 'index.asset.php' ) ) {
			return;
		}
		$asset_file = include $block_path . 'index.asset.php';
		wp_register_script(
			'vimeify-video-block-editor',
			$this->plugin->url() . 'blocks/dist/video/index.js',
			[ 'dgv-uploader' ] + $asset_file['dependencies'],
			$asset_file['version']
		);
		$default_folder  = '';
		$folders_enabled = (int) $this->plugin->system()->settings()->get( 'admin.gutenberg.enable_folders', 0 );
		if ( $folders_enabled ) {
			$folder = $this->plugin->system()->settings()->get_upload_profile_option_by_context( 'Backend.Editor.Gutenberg', 'folder' );
			if ( empty( $folder ) || 'default' === $folder ) {
				$default_folder = [ 'name' => __( 'Default (No folder)', 'wp-vimeo-videos' ), 'uri' => 'default' ];
			} else {
				$default_folder = [
					'name' => sprintf( '%s (Default)', $this->plugin->system()->cache()->remember( 'default_folder_name', function () use ( $folder ) {
						return $this->plugin->system()->vimeo()->get_folder_name( $folder );
					}, 30 * CacheInterface::MINUTE_IN_SECONDS ) ),
					'uri'  => $folder
				];
			}
		}
		wp_localize_script( 'vimeify-video-block-editor', 'VimeifyUploadBlock', [
			'nonce'               => wp_create_nonce( 'wp_rest' ),
			'methods'             => array(
				'upload' => __( 'Upload new Vimeo video', 'wp-vimeo-videos' ),
				'local'  => __( 'Insert Vimeo video from local library', 'wp-vimeo-videos' ),
				'search' => __( 'Search your Vimeo account', 'wp-vimeo-videos' ),
			),
			'upload_form_options' => array(
				'enable_view_privacy' => (int) $this->plugin->system()->settings()->get( 'admin.gutenberg.enable_view_privacy', 0 ),
				'enable_folders'      => (int) $this->plugin->system()->settings()->get( 'admin.gutenberg.enable_folders', 0 ),
				'privacy_view'        => $this->plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' ),
				'default_folder'      => $default_folder,
			),
			'i18n'                => array(
				'words'   => array(
					'block_name'    => __( 'Vimeify Upload', 'wp-vimeo-videos' ),
					'title'         => __( 'Title', 'wp-vimeo-videos' ),
					'description'   => __( 'Description', 'wp-vimeo-videos' ),
					'file'          => __( 'File', 'wp-vimeo-videos' ),
					'uploading3d'   => __( 'Uploading...', 'wp-vimeo-videos' ),
					'upload'        => __( 'Upload', 'wp-vimeo-videos' ),
					'search'        => __( 'Search', 'wp-vimeo-videos' ),
					'sorry'         => __( 'Sorry', 'wp-vimeo-videos' ),
					'view_privacy'  => __( 'View Privacy', 'wp-vimeo-videos' ),
					'folder'        => __( 'Folder', 'wp-vimeo-videos' ),
					'clear'         => __( 'Clear', 'wp-vimeo-videos' ),
					'save'          => __( 'Save', 'wp-vimeo-videos' ),
					'video_replace' => __( 'Replace Video', 'wp-vimeo-videos' ),
					'video_select'  => __( 'Select Video', 'wp-vimeo-videos' ),
					'video_list'    => __( 'Videos List', 'wp-vimeo-videos' ),
				),
				'phrases' => array(
					'upload_invalid_file'               => __( 'Please select valid video file.', 'wp-vimeo-videos' ),
					'invalid_search_phrase'             => __( 'Invalid search phrase. Please enter valid search phrase.', 'wp-vimeo-videos' ),
					'enter_phrase'                      => __( 'Enter phrase', 'wp-vimeo-videos' ),
					'select_video'                      => __( 'Select video', 'wp-vimeo-videos' ),
					'upload_success'                    => __( 'Video uploaded successfully!', 'wp-vimeo-videos' ),
					'block_title'                       => __( 'Insert Vimeo Video', 'wp-vimeo-videos' ),
					'existing_not_visible_current_user' => __( '= Uploaded by someone else, not visible to you =', 'wp-vimeo-videos' ),
					'radio_title'                       => __( "Upload/Select Vimeo Video", 'wp-vimeo-videos' ),
					'local_search_placeholder'          => __( 'Search your Local Library', 'wp-vimeo-videos' ),
					'remote_search_placeholder'         => __( 'Search your Vimeo.com account', 'wp-vimeo-videos' ),
					'folder_placeholder'                => __( 'Search for folders or leave blank', 'wp-vimeo-videos' ),
					'view_privacy_help'                 => __( 'Who will be able to view t his video' ),
					'folder_help'                       => __( 'Where this video should be uploaded to?', 'wp-vimeo-videos' ),
				),
			),
			'restBase'            => get_rest_url(),
			'accessToken'         => $this->plugin->system()->settings()->get( 'api_credentials.access_token' ),
			'notifyEndpoint'      => add_query_arg( [
				'action'   => 'dgv_store_upload',
				'_wpnonce' => wp_create_nonce( 'dgvsecurity' ),
				'source'   => 'Backend.Editor.Gutenberg',
			], admin_url( 'admin-ajax.php' ) )
		] );

		register_block_type( $block_path, array(
			'api_version'     => 3,
			'editor_script'   => 'vimeify-video-block-editor',
			'render_callback' => [ $this, 'render_block' ],
		) );
	}

	/**
	 * Registers block editor assets
	 * @return void
	 */
	public function register_block_editor_assets() {
		wp_register_style(
			'vimeify-video-block-editor',
			$this->plugin->url() . 'blocks/dist/video/index.css',
			array(),
			filemtime( $this->plugin->path() . 'blocks/dist/video/index.css' )
		);
	}

	/**
	 * Dynamic render for the upload block
	 *
	 * @param $block_attributes
	 * @param $content
	 *
	 * @return false|string
	 */
	public function render_block( $block_attributes, $content ) {
		if ( ! isset( $block_attributes['currentValue'] ) ) {
			return sprintf( '<p>%s</p>', __( 'No Vimeo.com video selected. Please edit this post and find the corresponding Vimeify Upload block to set video.', 'wp-vimeo-videos' ) );
		}

		$frm      = new VimeoFormatter();
		$uri      = $block_attributes['currentValue'];
		$video_id = $frm->uri_to_id( $uri );
		$post_id  = $this->plugin->system()->database()->get_post_id( $video_id );

		$view = apply_filters( 'dgv_view_video', null, $this->plugin );
		if ( is_null( $view ) ) {
			$view = new VideoView( $this->plugin );
		}
		$view->enqueue();

		if ( $post_id ) {
			return $view->output( [ 'post_id' => $post_id ] );
		} else {
			return $view->output( [ 'id' => $video_id ] );
		}
	}
}