<?php

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;
use Vimeify\Core\Utilities\Validators\FileValidator;
use Vimeo\Exceptions\VimeoRequestException;

class Hooks extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {

		add_filter( 'upload_mimes', [ $this, 'allowed_mime_types' ], 15 );
		add_action( 'wp_vimeo_upload_process_default_time_limit', [ $this, 'upload_process_default_time_limit' ], 10, 1 );
		add_action( 'init', [ $this, 'load_text_domain' ] );
		add_action( 'dgv_frontend_after_attempt_created', [ $this, 'frontend_after_attempt_created' ], 5, 3 );
		add_action( 'dgv_backend_after_upload', [ $this, 'backend_after_upload' ], 5 );

		$this->register_integrations();
	}

	/**
	 * Register integrations
	 * @return void
	 */
	public function register_integrations() {

		$integrations = $this->plugin->get_integrations();

		if ( empty( $integrations ) ) {
			return;
		}

		foreach ( $integrations as $integration ) {
			$integration->register();
		}
	}

	/**
	 * Enable custom extensions support
	 *
	 * @param $mimes
	 *
	 * @return mixed
	 */
	public function allowed_mime_types( $mimes ) {

		$file_validator = new FileValidator();

		foreach ( $file_validator->allowed_mimes() as $key => $mime ) {
			if ( ! isset( $mimes[ $key ] ) ) {
				$mimes[ $key ] = $mime;
			}
		}

		return $mimes;
	}

	/**
	 * Manually determine the default time limit of specific process
	 *
	 * @param $default
	 *
	 * @return int
	 * @since 1.4.0
	 */
	public function upload_process_default_time_limit( $default ) {
		$limit = (int) ini_get( 'max_execution_time' );
		if ( $limit === 0 ) {
			$default = 7200; // 2 hours.
		} elseif ( ( $limit - 10 ) < 0 ) {
			$default = 30;
		} else {
			$default = $limit - 10;
		}

		return $default;
	}

	/**
	 * Load the plugin textdomain
	 * @return void
	 */
	public function load_text_domain() {
		load_plugin_textdomain(
			$this->plugin->slug(),
			false,
			$this->plugin->path() . 'languages' . DIRECTORY_SEPARATOR
		);
	}

	/**
	 * Hook after the attempt was created.
	 *
	 * @param  int  $attempt
	 * @param  string  $uri
	 * @param  array  $args
	 *
	 * @throws VimeoRequestException
	 * @since 1.5.0
	 */
	public function frontend_after_attempt_created( $attempt, $uri, $args ) {

		$context = 'frontend';
		$logtag  = $this->get_logtag( $context );

		if ( isset( $args['process_hooks'] ) && ! $args['process_hooks'] ) {
			$this->plugin->system()->logger()->log( 'No hooks processing required. (frontend)', $logtag );

			return;
		}

		/**
		 * Make sure we are on the right track.
		 */
		if ( ! isset( $args['vimeo_id'] ) ) {
			$this->plugin->system()->logger()->log( 'No vimeo id found. Failed to execute post upload hooks. (frontend)', $logtag );

			return;
		}

		/*
		 * Singal start.
		 */
		$this->plugin->system()->logger()->log( sprintf( 'Processing hooks for %s', $uri ), $logtag );

		/**
		 * Set embed privacy
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_privacy() ) {
			$this->set_embed_privacy( $uri, $context );
		}

		/**
		 * Set Folder
		 */
		$folder_uri = $this->plugin->system()->settings()->get( 'folders.folder_frontend', 'default' );
		$this->set_folder( $uri, $folder_uri, $context );

		/**
		 * Set Presets
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_presets() ) {
			$preset_uri = $this->plugin->system()->settings()->get( 'embed_presets.preset_frontend', 'default' );
			$this->set_embed_preset( $uri, $preset_uri, $context );
		}

		/**
		 * Set View privacy
		 */
		$view_privacy = isset( $args['overrides']['view_privacy'] ) ? $args['overrides']['view_privacy'] : $this->get_view_privacy( $context );
		if ( $this->plugin->system()->vimeo()->supports_view_privacy_option( $view_privacy ) ) {
			$this->set_view_privacy( $uri, $view_privacy, $context );
		}

		/**
		 * Create local video (Save the video in the database if enabled.)
		 */
		if ( (int) $this->plugin->system()->settings()->get( 'frontend.behavior.store_in_library', 0 ) ) {
			$this->create_local_video( $args, $context );
		}
	}

	/**
	 * Handle after upload hook in Admin area
	 *
	 * @param $args
	 *
	 * @throws VimeoRequestException
	 * @since 1.7.0
	 */
	public function backend_after_upload( $args ) {

		$context = 'backend';
		$logtag  = $this->get_logtag( $context );

		/**
		 * Make sure we are on the right track.
		 */
		if ( ! isset( $args['vimeo_id'] ) ) {
			$this->plugin->system()->logger()->log( 'No vimeo id found. Failed to execute post upload hooks. (backend)', $logtag );

			return;
		}

		/**
		 * Obtain some important data.
		 */
		$response        = $args['vimeo_id'];
		$vimeo_formatter = new VimeoFormatter();
		$uri             = $vimeo_formatter->response_to_uri( $response );

		/**
		 * Signal start
		 */
		$this->plugin->system()->logger()->log( sprintf( 'Processing hooks for %s', $uri ), $logtag );

		/**
		 * Set embed privacy
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_privacy() ) {
			$this->set_embed_privacy( $uri, $context );
		}

		/**
		 * Set Folder
		 */
		$folder_uri = $this->plugin->system()->settings()->get( 'folders.folder_admin', 'default' );
		$this->set_folder( $uri, $folder_uri, $context );

		/**
		 * Set Presets
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_presets() ) {
			$preset_uri = $this->plugin->system()->settings()->get( 'embed_presets.preset_admin', 'default' );
			$this->set_embed_preset( $uri, $preset_uri, $context );
		}

		/**
		 * Set View privacy
		 */
		$view_privacy = isset( $args['overrides']['view_privacy'] ) ? $args['overrides']['view_privacy'] : $this->get_view_privacy( $context );
		if ( $this->plugin->system()->vimeo()->supports_view_privacy_option( $view_privacy ) ) {
			$this->set_view_privacy( $uri, $view_privacy, $context );
		}

		/**
		 * Create local video
		 */
		$this->create_local_video( $args, $context );

		/**
		 * Old deprecated hook
		 */
		do_action( 'dgv_after_upload', $uri, $this->plugin->system()->vimeo() );
	}

	/**
	 * Create local video
	 *
	 * @param $data
	 * @param $context
	 *
	 * @since 1.7.0
	 */
	protected function create_local_video( $data, $context ) {
		$logtag      = $this->get_logtag( $context );
		$id          = isset( $data['vimeo_id'] ) ? $data['vimeo_id'] : 0;
		$title       = isset( $data['vimeo_title'] ) ? $data['vimeo_title'] : '';
		$description = isset( $data['vimeo_description'] ) ? $data['vimeo_description'] : '';
		$post_id     = $this->plugin->system()->database()->create_local_video( $title, $description, $id, 'frontend' );
		$source      = isset( $data['source'] ) ? $data['source'] : array();

		if ( ! is_wp_error( $post_id ) ) {
			/**
			 * Update meta
			 */
			update_post_meta( $post_id, 'dgv_source', $source );
			if ( isset( $data['vimeo_size'] ) && $data['vimeo_size'] ) {
				update_post_meta( $post_id, 'dgv_size', (int) $data['vimeo_size'] );
			}

			/**
			 * Set link to the Video. Note: For some videos Vimeo creates non-standard links.
			 * e.g View privacy: Those with link only.
			 */
			if ( ! empty( $id ) ) {
				try {
					$response = $this->plugin->system()->vimeo()->get_video_by_id( $id, array( 'link' ) );
					if ( ! empty( $response['body']['link'] ) ) {
						update_post_meta( $post_id, 'dgv_link', $response['body']['link'] );
					}
				} catch ( \Exception $e ) {
				}
			}

			/**
			 * Set media library attachment source
			 */
			if ( isset( $data['source']['media_id'] ) ) {
				update_post_meta( $data['source']['media_id'], 'dgv', array(
					'vimeo_id' => $id,
					'local_id' => $post_id,
				) );
			}

			if ( ! empty( $data['vimeo_meta'] ) && is_array( $data['vimeo_meta'] ) ) {
				foreach ( $data['vimeo_meta'] as $k => $v ) {
					update_post_meta( $post_id, $k, $v );
				}
			}

			$this->plugin->system()->logger()->log( sprintf( 'Local video #%s created', $post_id ), $logtag );
		} else {
			$this->plugin->system()->logger()->log( sprintf( 'Failed to create local video (%s)', $post_id->get_error_message() ), $logtag );
		}
	}

	/**
	 * Set embed preset.
	 *
	 * @param $uri
	 * @param $preset_uri
	 * @param $context
	 *
	 * @throws VimeoRequestException
	 * @since 1.7.0
	 */
	protected function set_embed_preset( $uri, $preset_uri, $context ) {
		if ( empty( $preset_uri ) || 'default' === $preset_uri ) {
			return;
		}
		$logtag = $this->get_logtag( $context );
		try {
			$this->plugin->system()->vimeo()->set_video_embed_preset( $uri, $preset_uri );
			$this->plugin->system()->logger()->log( 'Embed preset set', $logtag );
		} catch ( VimeoRequestException $e ) {
			$this->plugin->system()->logger()->log( sprintf( 'Failed to set embed preset (%s)', $e->getMessage() ), $logtag );
		}
	}

	/**
	 * Set folder.
	 *
	 * @param $uri
	 * @param $folder_uri
	 * @param $context
	 *
	 * @since 1.7.0
	 */
	protected function set_folder( $uri, $folder_uri, $context ) {

		if ( empty( $folder_uri ) || 'default' === $folder_uri ) {
			return;
		}

		$logtag = $this->get_logtag( $context );

		try {
			$this->plugin->system()->vimeo()->set_video_folder( $uri, $folder_uri );
			$this->plugin->system()->logger()->log( 'Folder set', $logtag );
		} catch ( VimeoRequestException $e ) {
			$this->plugin->system()->logger()->log( sprintf( 'Failed to set folder (%s)', $e->getMessage() ), $logtag );
		}
	}

	/**
	 * Set embed privacy.
	 *
	 * @param $uri
	 * @param $context
	 *
	 * @since 1.7.0
	 */
	protected function set_embed_privacy( $uri, $context ) {
		$logtag = $this->get_logtag( $context );
		try {
			$whitelisted_domains = $this->plugin->system()->settings()->get_whitelisted_domains();
			if ( is_array( $whitelisted_domains ) && count( $whitelisted_domains ) > 0 ) {
				$this->plugin->system()->vimeo()->set_embed_privacy( $uri, 'whitelist' );
				foreach ( $whitelisted_domains as $domain ) {
					$this->plugin->system()->vimeo()->whitelist_domain_add( $uri, $domain );
					$this->plugin->system()->logger()->log( sprintf( 'Embed domain %s whitelisted for %s', $domain, $uri ), $logtag );
				}
			}
		} catch ( \Exception $e ) {
			$this->plugin->system()->logger()->log( sprintf( 'Failed to set embed privacy for %s. Error: (%s)', $uri, $e->getMessage() ), $logtag );
		}
	}

	/**
	 * Set view privacy
	 *
	 * @param $uri
	 * @param $privacy
	 * @param $context
	 */
	protected function set_view_privacy( $uri, $privacy, $context ) {

		$logtag = $this->get_logtag( $context );

		if ( ! in_array( $privacy, array( 'default', 'anybody' ) ) ) {
			$params['privacy'] = array( 'view' => $privacy );
			if ( $this->plugin->system()->vimeo()->can_edit() ) {
				try {
					$this->plugin->system()->vimeo()->edit( $uri, $params );
					$this->plugin->system()->logger()->log( sprintf( 'View privacy set to %s for %s', $privacy, $uri ), $logtag );
				} catch ( VimeoRequestException $e ) {
					$this->plugin->system()->logger()->log( sprintf( 'Failed to set view privacy %s for %s. Error: (%s)', $privacy, $uri, $e->getMessage() ), $logtag );
				}
			} else {
				$this->plugin->system()->logger()->log( sprintf( 'Failed to set view privacy %s for %s. Unsupported on %s plan', $privacy, $uri, $this->plugin->system()->vimeo()->get_plan( true ) ), $logtag );
			}
		}
	}

	/**
	 * Return view privacy.
	 *
	 * @param $context
	 *
	 * @return string|null
	 */
	protected function get_view_privacy( $context ) {
		if ( $context === 'frontend' ) {
			$privacy = $this->plugin->system()->settings()->get_default_frontend_view_privacy();
		} elseif ( $context === 'backend' ) {
			$privacy = $this->plugin->system()->settings()->get_default_admin_view_privacy();
		} else {
			$privacy = null;
		}

		return $privacy;
	}

	/**
	 * Returns the log tag.
	 *
	 * @param $context
	 *
	 * @return string
	 * @since 1.7.0
	 */
	protected function get_logtag( $context ) {
		if ( $context === 'backend' ) {
			$tag = 'DGV-ADMIN-HOOKS';
		} elseif ( $context === 'frontend' ) {
			$tag = 'DGV-FRONTEND-HOOKS';
		} else {
			$tag = 'DGV-INTERNAL-HOOKS';
		}

		return $tag;
	}
}