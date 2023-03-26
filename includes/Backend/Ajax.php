<?php

namespace Vimeify\Core\Backend;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Utilities\Formatters\ByteFormatter;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;
use Vimeify\Core\Utilities\Validators\NetworkValidator;

class Ajax extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {

		add_action( 'wp_ajax_dgv_handle_upload', [ $this, 'handle_upload' ] );
		add_action( 'wp_ajax_dgv_handle_delete', [ $this, 'handle_delete' ] );
		add_action( 'wp_ajax_dgv_store_upload', [ $this, 'store_upload' ] );
		add_action( 'wp_ajax_dgv_handle_basic_edit', [ $this, 'handle_basic_edit' ] );
		add_action( 'wp_ajax_dgv_handle_embed_privacy', [ $this, 'handle_embed_privacy' ] );
		add_action( 'wp_ajax_dgv_delete_embed_privacy_domain', [ $this, 'handle_embed_privacy_whitelist_remove' ] );
		add_action( 'wp_ajax_dgv_handle_video_embed_preset_set', [ $this, 'handle_video_embed_preset_set' ] );
		add_action( 'wp_ajax_dgv_handle_video_folder_set', [ $this, 'handle_video_folder_set' ] );
		add_action( 'wp_ajax_dgv_get_uploads', [ $this, 'get_uploads' ] );
		add_action( 'wp_ajax_dgv_attachment2vimeo', [ $this, 'handle_attachment2vimeo' ] );
		add_action( 'wp_ajax_dgv_attachment2vimeo_delete', [ $this, 'handle_attachment2vimeo_delete' ] );
		add_action( 'wp_ajax_dgv_user_search', [ $this, 'handle_user_search' ] );
		add_action( 'wp_ajax_dgv_folder_search', [ $this, 'handle_folder_search' ] );
		add_action( 'wp_ajax_dgv_embed_preset_search', [ $this, 'handle_embed_preset_search' ] );
		add_action( 'wp_ajax_dgv_generate_stats', [ $this, 'handle_generate_stats' ] );

	}

	/**
	 * Store upload on the server
	 */
	public function store_upload() {

		$logtag = 'DGV-EDITOR-UPLOAD';

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unauthorized action. User must be Author, Editor or Administrator to edit.', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		if ( ! $this->plugin->system()->requests()->is_http_post() ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid request.', 'wp-vimeo-videos-pro' ),
			) );
		}

		$title       = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : __( 'Untitled', 'wp-vimeo-videos-pro' );
		$description = isset( $_POST['description'] ) ? sanitize_text_field( $_POST['description'] ) : '';
		$size        = isset( $_POST['size'] ) ? intval( $_POST['size'] ) : false;
		$meta        = isset( $_POST['meta'] ) ? $_POST['meta'] : null;
		$uri         = sanitize_text_field( $_POST['uri'] );


		$vimeo_formatter = new VimeoFormatter();
		$video_id        = $vimeo_formatter->uri_to_id( $uri );

		if ( is_string( $meta ) ) {
			$meta = @json_decode( $meta, true );
		}

		/**
		 * Upload success hook
		 */
		do_action( 'dgv_backend_after_upload', array(
			'vimeo_title'       => $title,
			'vimeo_description' => $description,
			'vimeo_id'          => $video_id,
			'vimeo_size'        => $size,
			'vimeo_meta'        => $meta,
			'source'            => array(
				'software' => 'Editor',
			),
		) );

		$this->plugin->system()->logger()->log( sprintf( 'Video %s stored.', $uri ), $logtag );

		wp_send_json_success( array(
			'message' => __( 'Video uploaded successfully.', 'wp-vimeo-videos-pro' ),
		) );

		exit;

	}

	/**
	 * Handles video deletion
	 */
	public function handle_delete() {

		$logtag = 'DGV-ADMIN-EDIT';

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'title'   => __( 'Permission denied', 'wp-vimeo-videos-pro' ),
				'message' => __( 'Security check failed. Please contact administrator.' )
			) );
		}

		$required_cap = apply_filters( 'dgv_vimeo_cap_delete', 'delete_posts' );

		if ( ! current_user_can( $required_cap ) ) {
			wp_send_json_error( array(
				'title'   => __( 'Permission denied', 'wp-vimeo-videos-pro' ),
				'message' => __( 'Your WordPress account doesn\'t have the required permissions do delete video.' )
			) );
			exit;
		}
		if ( ! $this->plugin->system()->vimeo()->can_delete() ) {
			wp_send_json_error( array(
				'title'   => __( 'Permission denied', 'wp-vimeo-videos-pro' ),
				'message' => __( 'Your vimeo account doesn\'t have the delete scope to perform this action.' )
			) );
			exit;
		}

		if ( ! isset( $_POST['vimeo_uri'] ) || empty( $_POST['vimeo_uri'] ) ) {

			$args = array(
				'title'   => __( 'Missing video', 'wp-vimeo-videos-pro' ),
				'message' => __( 'Select valid vimeo video to delete.' )
			);

			if ( isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
				$deleted = wp_delete_post( $_POST['post_id'], 1 );
				if ( $deleted ) {
					$args['title']        = __( 'Success' );
					$args['message']      = __( 'Video deleted successfully.' );
					$args['local_delete'] = 1;
					wp_send_json_success( $args );
					exit;
				}
			}

			wp_send_json_error( $args );


			exit;
		}


		$vimeo_uri         = $this->plugin->system()->vimeo()->formatter->id_to_uri( sanitize_text_field( $_POST['vimeo_uri'] ) );
		$vimeo_post_id     = $this->plugin->system()->database()->get_post_id( $vimeo_uri );
		$vimeo_post_exists = ( false !== get_post_status( $vimeo_post_id ) );

		try {
			$response = $this->plugin->system()->vimeo()->delete( $vimeo_uri );

			if ( $response['status'] !== 204 && ( isset( $response['body']['error'] ) && ! empty( $response['body']['error'] ) ) ) {
				$local_delete = false;
				if ( $vimeo_post_exists ) {
					$local_delete = true;
					wp_delete_post( $vimeo_post_id, true );
					$this->plugin->system()->logger()->log( sprintf( 'Local video %s deleted', $vimeo_post_id ), $logtag );
				}
				$this->plugin->system()->logger()->log( sprintf( 'Unable to delete remote video %s', $vimeo_uri ), $logtag );
				wp_send_json_error( array(
					'title'        => __( 'Sorry!', 'wp-vimeo-videos-pro' ),
					'message'      => $response['body']['error'],
					'local_delete' => $local_delete,
				) );
			} elseif ( $response['status'] === 204 ) {
				$local_delete = false;
				if ( $vimeo_post_exists ) {
					$local_delete = true;
					wp_delete_post( $vimeo_post_id, true );
					$this->plugin->system()->logger()->log( sprintf( 'Local video %s deleted', $vimeo_post_id ), $logtag );
				}
				$this->plugin->system()->logger()->log( sprintf( 'Remote video %s deleted', $vimeo_uri ), $logtag );
				wp_send_json_success( array(
					'title'        => __( 'Success', 'wp-vimeo-videos-pro' ),
					'message'      => __( 'Video deleted successfully!', 'wp-vimeo-videos-pro' ),
					'local_delete' => $local_delete
				) );
			} else {
				$this->plugin->system()->logger()->log( sprintf( 'Unable to delete remote video %s', $vimeo_uri ), $logtag );
				wp_send_json_error( array(
					'title'   => __( 'Sorry!', 'wp-vimeo-videos-pro' ),
					'message' => __( 'The video was not deleted.', 'wp-vimeo-videos-pro' )
				) );
			}

		} catch ( \Exception $e ) {
			$this->plugin->system()->logger()->log( sprintf( 'Unable to delete remote video %s. Local %s video deleted. Error: %s', $vimeo_uri, $vimeo_post_id, $e->getMessage() ), $logtag );
			wp_delete_post( $vimeo_post_id, true );
			wp_send_json_success( array(
				'title'        => __( 'Success', 'wp-vimeo-videos-pro' ),
				'message'      => __( 'Video deleted successfully. However we had a trouble deleting the vimeo from vimeo.com. It may be deleted or belongs to different account.', 'wp-vimeo-videos-pro' ),
				'local_delete' => true,
			) );
			exit;
		}
	}

	/**
	 * Handle basic edit vimeo
	 */
	public function handle_basic_edit() {

		$logtag = 'DGV-ADMIN-EDIT';

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unauthorized action', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		if ( ! $this->plugin->system()->requests()->is_http_post() ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid request', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		$uri          = isset( $_POST['uri'] ) ? $_POST['uri'] : '';
		$name         = isset( $_POST['name'] ) ? $_POST['name'] : '';
		$description  = isset( $_POST['description'] ) ? $_POST['description'] : '';
		$view_privacy = isset( $_POST['view_privacy'] ) ? $_POST['view_privacy'] : '';

		if ( empty( $name ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please enter valid name', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		try {

			/**
			 * Basic details
			 */
			$params = array(
				'name'        => $name,
				'description' => $description,
			);

			/**
			 * Set privacy
			 */
			$privacy = $this->get_view_privacy( $view_privacy );
			if ( ! empty( $privacy ) ) {
				if ( 'default' !== $privacy ) {
					$params['privacy'] = array( 'view' => $privacy );
				}
			}

			/**
			 * Update video
			 */
			$response = $this->plugin->system()->vimeo()->edit( $uri, $params );
			$post_ID  = $this->plugin->system()->database()->get_post_id( $uri );

			/**
			 * Respond back
			 */
			if ( isset( $response['status'] ) ) {
				if ( $response['status'] === 200 ) {
					wp_update_post( array(
						'ID'         => $post_ID,
						'post_title' => $name,
					) );
					$this->plugin->system()->logger()->log( sprintf( 'Video "%s" saved', $uri ), $logtag );
					wp_send_json_success( array(
						'message' => __( 'Video saved successfully', 'wp-vimeo-videos-pro' )
					) );
				} else {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to save video %s', $uri ), $logtag );
					wp_send_json_error( array(
						'message' => __( 'Error saving the video', 'wp-vimeo-videos-pro' )
					) );
				}
			} else {
				$this->plugin->system()->logger()->log( sprintf( 'Unable to save video %s', $uri ), $logtag );
				wp_send_json_error( array(
					'message' => __( 'Unknown error.', 'wp-vimeo-videos-pro' ),
				) );
			}

		} catch ( VimeoRequestException $e ) {

			$this->plugin->system()->logger()->log( sprintf( 'Unable to save video  %s', $uri ), $logtag );

			wp_send_json_error( array(
				'message' => $e->getMessage(),
			) );

		}

		exit;

	}

	/**
	 * Handle embed privacy
	 */
	public function handle_embed_privacy() {

		$logtag = 'DGV-ADMIN-EDIT';

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unauthorized action', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		if ( ! $this->plugin->system()->requests()->is_http_post() ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid request', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		$uri = isset( $_POST['uri'] ) ? $_POST['uri'] : '';

		$privacy_embed = isset( $_POST['privacy_embed'] ) ? $_POST['privacy_embed'] : '';

		if ( empty( $privacy_embed ) || ! in_array( $privacy_embed, array( 'public', 'whitelist' ) ) ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid embed privacy mehtod.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}

		$whitelist_domain = isset( $_POST['privacy_embed_domain'] ) ? $_POST['privacy_embed_domain'] : '';

		try {
			$network_validator = new NetworkValidator();
			$this->plugin->system()->vimeo()->set_embed_privacy( $uri, $privacy_embed );
			if ( 'whitelist' === $privacy_embed && ! empty( $whitelist_domain ) ) {
				if ( ! $network_validator->validate_domain_name( $whitelist_domain ) ) {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to whitelist domain %s for %s. Error: Invalid format', $whitelist_domain, $uri ), $logtag );
					wp_send_json_error( array(
						'message' => __( 'Invalid domain. Please enter valid domain name.', 'wp-vimeo-videos-pro' ),
					) );
					exit;
				} else {
					$whitelist_response = $this->plugin->system()->vimeo()->whitelist_domain_add( $uri, $whitelist_domain );
					$this->plugin->system()->logger()->log( sprintf( 'Domain %s added to whitelist for %s', $whitelist_domain, $uri ), $logtag );
					if ( isset( $whitelist_response['status'] ) ) {
						if ( $whitelist_response['status'] >= 200 && $whitelist_response['status'] < 300 ) {
							wp_send_json_success( array(
								'message'      => __( 'Domain added to embed whitelist.', 'wp-vimeo-videos-pro' ),
								'domain_added' => $whitelist_domain,
								'uri'          => $uri,
							) );
							exit;
						} else {
							wp_send_json_error( array(
								'message' => __( 'Failed to add domain to the embed whitelist.', 'wp-vimeo-videos-pro' ),
							) );
							exit;
						}
					} else {
						wp_send_json_error( array(
							'message' => __( 'Invalid response received from vimeo', 'wp-vimeo-videos-pro' )
						) );
						exit;
					}
				}
			} else {
				wp_send_json_success( array(
					'message' => __( 'Embed privacy changed successfully.', 'wp-vimeo-videos-pro' ),
				) );
				exit;
			}
		} catch ( \Exception $e ) {
			$message = 'whitelist' === $privacy_embed ?
				sprintf( 'Unable to whitelist domain %s for %s. Error: %s', $whitelist_domain, $uri, $e->getMessage() ) :
				sprintf( 'Unable to change embed privacy for %s. Error: %s', $uri, $e->getMessage() );
			wp_send_json_error( array(
				'message' => $message,
			) );
			exit;
		}
	}

	/**
	 * Handles whitelist removal
	 */
	public function handle_embed_privacy_whitelist_remove() {

		$logtag = 'DGV-ADMIN-EDIT';

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unauthorized action', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		if ( ! $this->plugin->system()->requests()->is_http_post() ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid request', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		$uri = isset( $_POST['uri'] ) ? $_POST['uri'] : '';

		if ( empty( $uri ) ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid video URI specified', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		$whitelist_domain = isset( $_POST['domain'] ) ? $_POST['domain'] : '';

		try {
			$whitelist_response = $this->plugin->system()->vimeo()->whitelist_domain_remove( $uri, $whitelist_domain );
			$this->plugin->system()->logger()->log( sprintf( 'Domain %s whitelisted for %s', $whitelist_domain, $uri ), $logtag );
		} catch ( VimeoRequestException $e ) {
			$this->plugin->system()->logger()->log( sprintf( 'Error removing domain from whitelist (%s)', $e->getMessage() ), $logtag );
			wp_send_json_success( array(
				'message' => __( 'Error removing domain from whitelist.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}
		if ( isset( $whitelist_response['status'] ) ) {
			if ( $whitelist_response['status'] === 204 ) {
				wp_send_json_success( array(
					'message' => __( 'Domain removed successfully', 'wp-vimeo-videos-pro' ),
				) );
			} else {
				wp_send_json_error( array(
					'message' => isset( $whitelist_response['body']['error'] ) ? $whitelist_response['body']['error'] : __( 'Failed to remove domain from embed privacy whitelist.', 'wp-vimeo-videos-pro' )
				) );
				exit;
			}
		} else {
			wp_send_json_error( array(
				'message' => __( 'Invalid response received from vimeo', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}
	}

	/**
	 * Handles Video folder update. Remove or add video to folder.
	 */
	public function handle_video_folder_set() {

		$logtag = 'DGV-ADMIN-EDIT';

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		} else {

			$video_uri       = isset( $_POST['video_uri'] ) ? $_POST['video_uri'] : null;
			$folder_uri      = isset( $_POST['folder_uri'] ) ? $_POST['folder_uri'] : null;
			$vimeo_formatter = new VimeoFormatter();
			$video_id        = $vimeo_formatter->uri_to_id( $video_uri );

			if ( empty( $video_uri ) ) {
				wp_send_json_error( array(
					'message' => __( 'Video missing.', 'wp-vimeo-videos-pro' ),
				) );
				exit;
			}
			if ( empty( $folder_uri ) ) {
				wp_send_json_error( array(
					'message' => __( 'Folder missing.', 'wp-vimeo-videos-pro' ),
				) );
				exit;
			}

			// Handle default
			if ( $folder_uri === 'default' ) {
				$video      = $this->plugin->system()->vimeo()->get( '/videos/' . $video_id . '?fields=parent_folder' );
				$folder_uri = isset( $video['body']['parent_folder']['uri'] ) && ! empty( $video['body']['parent_folder']['uri'] ) ? $video['body']['parent_folder']['uri'] : null;
				if ( $folder_uri ) {
					try {
						$response = $this->plugin->system()->vimeo()->remove_video_folder( $video_uri, $folder_uri );
						$this->plugin->system()->logger()->log( sprintf( 'Folder changed to %s for %s', 'default', $video_uri ), $logtag );
						if ( isset( $response['status'] ) ) {
							if ( in_array( $response['status'], array( 200, 204 ) ) ) {
								$this->plugin->system()->logger()->log( sprintf( 'Folder changed to %s for %s', 'default', $video_uri ), $logtag );
								wp_send_json_success( array(
									'message' => __( 'Video removed from folder successfully.', 'wp-vimeo-videos-pro' ),
								) );
							} else {
								$error = '';
								if ( isset( $response['body']['error'] ) ) {
									$error = sprintf( 'Error: %s', $response['body']['error'] );
								}
								$this->plugin->system()->logger()->log( sprintf( 'Unable to change folder to %s for %s. %s', 'default', $video_uri, $error ), $logtag );
								wp_send_json_success( array(
									'message' => sprintf( __( 'Unable to change folder. %s', 'wp-vimeo-videos-pro' ), $error ),
								) );
							}
						} else {
							$this->plugin->system()->logger()->log( sprintf( 'Unable to set folder %s for %s. Error: Unreadable response', 'default', $video_uri ), $logtag );
							wp_send_json_error( array(
								'message' => __( 'Invalid response received from vimeo', 'wp-vimeo-videos-pro' ),
							) );
						}
					} catch ( VimeoRequestException $e ) {
						$this->plugin->system()->logger()->log( sprintf( 'Unable to remove folder %s for %s. Error: %s', $folder_uri, $video_uri, $e->getMessage() ), $logtag );
						wp_send_json_error( array(
							'message' => $e->getMessage(),
						) );
					}
				} else {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to set folder %s for %s. Error: Invalid folder', $folder_uri, $video_uri ), $logtag );
					wp_send_json_error( array(
						'message' => __( 'Unexpected error! Sorry.', 'wp-vimeo-videos-pro' ),
					) );
				}
			} else { // Handle folder
				try {

					$response = $this->plugin->system()->vimeo()->set_video_folder( $video_uri, $folder_uri );
					if ( isset( $response['status'] ) ) {
						if ( in_array( $response['status'], array( 200, 204 ) ) ) {
							$this->plugin->system()->logger()->log( sprintf( 'Folder changed to %s for %s', $folder_uri, $video_uri ), $logtag );
							wp_send_json_success( array(
								'message' => __( 'Video moved to folder successfully.', 'wp-vimeo-videos-pro' ),
							) );
						} else {
							$error = '';
							if ( isset( $response['body']['error'] ) ) {
								$error = sprintf( 'Error: %s', $response['body']['error'] );
							}
							$this->plugin->system()->logger()->log( sprintf( 'Unable to change folder to %s for %s. %s', $folder_uri, $video_uri, $error ), $logtag );
							wp_send_json_success( array(
								'message' => sprintf( __( 'Unable to change folder. %s', 'wp-vimeo-videos-pro' ), $error ),
							) );
						}
					} else {
						$this->plugin->system()->logger()->log( sprintf( 'Unable to set folder %s for %s. Error: Unreadable response', $folder_uri, $video_uri ), $logtag );
						wp_send_json_error( array(
							'message' => __( 'Invalid response received from vimeo', 'wp-vimeo-videos-pro' ),
						) );
					}

				} catch ( VimeoRequestException $e ) {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to set folder %s for %s. Error: %s', $folder_uri, $video_uri, $e->getMessage() ), $logtag );
					wp_send_json_error( array(
						'message' => $e->getMessage(),
					) );
				}
			}
			exit;
		}
	}

	/**
	 * Handles Video embed preset update. Remove or add embed preset to video.
	 */
	public function handle_video_embed_preset_set() {

		$logtag = 'DGV-ADMIN-EDIT';

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		} else {

			$video_uri       = isset( $_POST['video_uri'] ) ? $_POST['video_uri'] : null;
			$preset_uri      = isset( $_POST['embed_preset_uri'] ) ? $_POST['embed_preset_uri'] : null;
			$vimeo_formatter = new VimeoFormatter();
			$video_id        = $vimeo_formatter->uri_to_id( $video_uri );

			if ( empty( $video_uri ) ) {
				wp_send_json_error( array(
					'message' => __( 'Video missing.', 'wp-vimeo-videos-pro' ),
				) );
				exit;
			}
			if ( empty( $preset_uri ) ) {
				wp_send_json_error( array(
					'message' => __( 'Preset missing.', 'wp-vimeo-videos-pro' ),
				) );
				exit;
			}

			// Handle default
			if ( $preset_uri === 'default' ) {
				$video      = $this->plugin->system()->vimeo()->get( '/videos/' . $video_id . '?fields=embed' );
				$preset_uri = isset( $video['body']['embed']['uri'] ) && ! empty( $video['body']['embed']['uri'] ) ? $video['body']['embed']['uri'] : null;
				if ( $preset_uri ) {
					try {
						$response = $this->plugin->system()->vimeo()->remove_video_embed_preset( $video_uri, $preset_uri );
						if ( isset( $response['status'] ) ) {
							if ( in_array( $response['status'], array( 200, 204 ) ) ) {
								$this->plugin->system()->logger()->log( sprintf( 'Embed preset changed to %s for %s', 'default', $video_uri ), $logtag );
								wp_send_json_success( array(
									'message' => __( 'Embed preset removed from video successfully', 'wp-vimeo-videos-pro' ),
								) );
							} else {
								$error = '';
								if ( isset( $response['body']['error'] ) ) {
									$error = sprintf( 'Error: %s', $response['body']['error'] );
								}
								$this->plugin->system()->logger()->log( sprintf( 'Unable to change embed preset to %s for %s. %s', 'default', $video_uri, $error ), $logtag );
								wp_send_json_success( array(
									'message' => sprintf( __( 'Unable to change embed preset. %s', 'wp-vimeo-videos-pro' ), $error ),
								) );
							}
						} else {
							$this->plugin->system()->logger()->log( sprintf( 'Unable to set embed preset %s for %s. Error: Unreadable response.', 'default', $video_uri ), $logtag );
							wp_send_json_error( array(
								'message' => __( 'Invalid response received from vimeo', 'wp-vimeo-videos-pro' ),
							) );
						}
					} catch ( VimeoRequestException $e ) {
						$this->plugin->system()->logger()->log( sprintf( 'Unable to remove embed preset %s for %s. Error: %s', $preset_uri, $video_uri, $e->getMessage() ), $logtag );
						wp_send_json_error( array(
							'message' => $e->getMessage(),
						) );
					}
				} else {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to set embed preset %s for %s. Error: Invalid preset.', $preset_uri, $video_uri ), $logtag );
					wp_send_json_error( array(
						'message' => __( 'Unexpected error! Sorry.', 'wp-vimeo-videos-pro' ),
					) );
				}
			} else { // Handle Preset
				try {

					$response = $this->plugin->system()->vimeo()->set_video_embed_preset( $video_uri, $preset_uri );
					if ( isset( $response['status'] ) ) {
						if ( in_array( $response['status'], array( 200, 204 ) ) ) {
							$this->plugin->system()->logger()->log( sprintf( 'Embed preset changed to %s for %s', $preset_uri, $video_uri ), $logtag );
							wp_send_json_success( array(
								'message' => __( 'Embed preset added to Video successfully.', 'wp-vimeo-videos-pro' ),
							) );
						} else {
							$error = '';
							if ( isset( $response['body']['error'] ) ) {
								$error = sprintf( 'Error: %s', $response['body']['error'] );
							}
							$this->plugin->system()->logger()->log( sprintf( 'Unable to change embed preset to %s for %s. %s', $preset_uri, $video_uri, $error ), $logtag );
							wp_send_json_success( array(
								'message' => sprintf( __( 'Unable to change embed preset. %s', 'wp-vimeo-videos-pro' ), $error ),
							) );
						}
					} else {
						$this->plugin->system()->logger()->log( sprintf( 'Unable to set embed preset %s for %s. Error: Unraedable response', $preset_uri, $video_uri ), $logtag );
						wp_send_json_error( array(
							'message' => __( 'Invalid response received from vimeo', 'wp-vimeo-videos-pro' ),
						) );
					}

				} catch ( VimeoRequestException $e ) {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to set embed preset %s for %s. Error: %s', $preset_uri, $video_uri, $e->getMessage() ), $logtag );
					wp_send_json_error( array(
						'message' => $e->getMessage(),
					) );
				}
			}
			exit;

		}
	}

	/**
	 * Handles attachment upload
	 */
	public function handle_attachment2vimeo() {

		$logtag = 'DGV-ADMIN-A2V';

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ) ) );
			exit;
		}

		if ( ! $this->plugin->system()->requests()->is_http_post() ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request', 'wp-vimeo-videos-pro' ) ) );
			exit;
		}

		$ID        = isset( $_POST['attachment_id'] ) ? sanitize_text_field( $_POST['attachment_id'] ) : null;
		$title     = isset( $_POST['vimeo_title'] ) && strlen( $_POST['vimeo_title'] ) > 0 ? $_POST['vimeo_title'] : null;
		$desc      = isset( $_POST['vimeo_description'] ) && strlen( $_POST['vimeo_description'] ) > 0 ? $_POST['vimeo_description'] : null;
		$view_priv = isset( $_POST['vimeo_view_privacy'] ) && ! empty( $_POST['vimeo_view_privacy'] ) ? $_POST['vimeo_view_privacy'] : null;

		if ( is_null( $ID ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid attachment', 'wp-vimeo-videos-pro' ) ) );
			exit;
		}

		if ( is_null( $title ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter valid video title.', 'wp-vimeo-videos-pro' ) ) );
			exit;
		}

		$file_path = get_attached_file( $ID );
		$file_size = file_exists( $file_path ) ? filesize( $file_path ) : false;

		try {

			$params = array(
				'name' => $title,
			);

			if ( ! is_null( $desc ) ) {
				$params['description'] = $desc;
			}

			/**
			 * Perform the upload
			 */
			$result          = $this->plugin->system()->vimeo()->upload( $file_path, $params );
			$vimeo_uri       = $result['response'];
			$vimeo_formatter = new VimeoFormatter();
			$vimeo_id        = $vimeo_formatter->uri_to_id( $vimeo_uri );

			/**
			 * Upload success hook
			 */
			do_action( 'dgv_backend_after_upload', array(
				'vimeo_title'       => $title,
				'vimeo_description' => $desc,
				'vimeo_id'          => $vimeo_id,
				'vimeo_size'        => $file_size,
				'overrides'         => array(
					'view_privacy' => $view_priv,
				),
				'source'            => array(
					'software' => 'Media Table',
					'media_id' => $ID,
				)
			) );

			$this->plugin->system()->logger()->log( sprintf( 'Media library video #%s uploaded to Vimeo', $ID ), $logtag );

			wp_send_json_success( array(
				'vimeo_id'          => $vimeo_id,
				'info_metabox_html' => $this->plugin->system()->views()->get_view( 'admin/partials/media-buttons', [
					'id'     => $ID,
					'plugin' => $this->plugin,
				] )
			) );


		} catch ( \Exception $e ) {
			$this->plugin->system()->logger()->log( sprintf( 'Failed to uplaod from media library screen (%s)', $e->getMessage() ), $logtag );
			wp_send_json_error( __( 'Upload failed: ' . $e->getMessage(), 'wp-vimeo-videos-pro' ) );
		}
		exit;
	}

	/**
	 * Handles attachment delete
	 */
	public function handle_attachment2vimeo_delete() {

		$logtag = 'DGV-ADMIN-A2V';

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ) ) );
			exit;
		}

		if ( ! $this->plugin->system()->requests()->is_http_post() ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request', 'wp-vimeo-videos-pro' ) ) );
			exit;
		}

		$ID = isset( $_POST['attachment_id'] ) ? sanitize_text_field( $_POST['attachment_id'] ) : null;

		if ( is_null( $ID ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid attachment', 'wp-vimeo-videos-pro' ) ) );
			exit;
		}

		$meta = get_post_meta( $ID, 'dgv', true );

		if ( ! isset( $meta['vimeo_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid attachment', 'wp-vimeo-videos-pro' ) ) );
			exit;
		} else {
			try {
				$uri      = '/videos/' . $meta['vimeo_id'];
				$response = $this->plugin->system()->vimeo()->delete( $uri );
				$local_id = isset( $meta['local_id'] ) ? (int) $meta['local_id'] : 0;
				if ( $local_id > 0 ) {
					wp_delete_post( $local_id, true );
				}
				delete_post_meta( $ID, 'dgv' );
				$this->plugin->system()->logger()->log( sprintf( 'Media library video %s deleted from Vimeo', $uri ), $logtag );
				wp_send_json_success( array(
					'message'           => __( 'Video deleted from vimeo successfully' ),
					'info_metabox_html' => $this->plugin->system()->views()->get_view( 'admin/partials/media-buttons', [
						'id'     => $ID,
						'plugin' => $this->plugin,
					] ),
					'data' => json_encode( $response )
				) );

			} catch ( \Exception $e ) {
				$this->plugin->system()->logger()->log( sprintf( 'Unable to delete from media library screen (%s)', $e->getMessage() ), $logtag );
				wp_send_json_error( array( 'message' => __( 'Invalid attachment', 'wp-vimeo-videos-pro' ) ) );
				exit;
			}
		}

	}

	/**
	 * Return the uploaded videos
	 */
	public function get_uploads() {

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unauthorized action', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		if ( ! $this->plugin->system()->requests()->is_http_get() ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid request', 'wp-vimeo-videos-pro' )
			) );
			exit;
		}

		$current_user_uploads = ! current_user_can( 'administrator' ) && (int) $this->plugin->system()->settings()->get( 'admin.tinymce.show_author_uploads_only' );

		$uplaods = $this->plugin->system()->database()->get_uploaded_videos( $current_user_uploads );

		wp_send_json_success( array(
			'uploads' => $uplaods,
		) );
		exit;

	}

	/**
	 * Handles user search
	 */
	public function handle_user_search() {

		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}

		$items    = array();
		$phrase   = isset( $_REQUEST['search_str'] ) ? sanitize_text_field( $_REQUEST['search_str'] ) : '';
		$page_num = isset( $_REQUEST['page_number'] ) ? intval( $_REQUEST['page_number'] ) : 1;
		$per_page = 25;

		$params = array(
			'number' => $per_page,
			'paged'  => $page_num,
		);
		if ( ! empty( $phrase ) ) {
			$params['search']         = '*' . esc_attr( $phrase ) . '*';
			$params['search_columns'] = array(
				'user_login',
				'user_nicename',
				'user_email',
				'user_url',
			);
		}
		$query = new \WP_User_Query( $params );
		foreach ( $query->get_results() as $user ) {
			$items[] = array(
				'id'   => $user->ID,
				'text' => sprintf( '%s (#%d - %s)', $user->user_nicename, $user->ID, $user->user_email )
			);
		}

		wp_send_json_success( [
			'results'    => $items,
			'pagination' => [
				'more' => ! empty( $items )
			]
		] );

	}

	/**
	 * Handles folder search
	 * @return void
	 */


	public function handle_folder_search() {
		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}

		$phrase   = isset( $_REQUEST['search_str'] ) ? sanitize_text_field( $_REQUEST['search_str'] ) : '';
		$page_num = isset( $_REQUEST['page_number'] ) ? intval( $_REQUEST['page_number'] ) : 1;
		$per_page = 25;

		$request = [
			'per_page' => $per_page,
			'page'     => $page_num,
			'query'    => $phrase
		];

		try {
			$folders_response = $this->plugin->system()->vimeo()->get_folders_query( $request );
		} catch ( \Exception $e ) {
		}

		$data  = ! empty( $folders_response['results'] ) ? $folders_response['results'] : [];
		$items = [
			[
				'id'   => 'default',
				'text' => __( 'Default (no folder)', 'wp-vimeo-videos-pro' )
			]
		];
		foreach ( $data as $entry ) {
			$items[] = [
				'id'   => $entry['uri'],
				'text' => $entry['name'],
			];
		}

		wp_send_json_success( [
			'results'    => $items,
			'pagination' => [
				'more' => isset( $folders_response['paging']['has_more'] ) ? $folders_response['paging']['has_more'] : false,
			]
		] );
	}

	/**
	 * Handles the embed preset search
	 * @return void
	 * @since 2.0.0
	 */
	public function handle_embed_preset_search() {
		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}

		$phrase   = isset( $_REQUEST['search_str'] ) ? sanitize_text_field( $_REQUEST['search_str'] ) : '';
		$page_num = isset( $_REQUEST['page_number'] ) ? intval( $_REQUEST['page_number'] ) : 1;
		$per_page = 25;

		$request = [
			'per_page' => $per_page,
			'page'     => $page_num,
			'query'    => $phrase
		];

		try {
			$presets_response = $this->plugin->system()->vimeo()->get_embed_presets_query( $request );
		} catch ( \Exception $e ) {
		}

		$data  = ! empty( $presets_response['results'] ) ? $presets_response['results'] : [];
		$items = [
			[
				'id'   => 'default',
				'text' => __( 'Default (no preset)', 'wp-vimeo-videos-pro' )
			]
		];
		foreach ( $data as $entry ) {
			$items[] = [
				'id'   => $entry['uri'],
				'text' => $entry['name'],
			];
		}

		wp_send_json_success( [
			'results'    => $items,
			'pagination' => [
				'more' => isset( $presets_response['paging']['has_more'] ) ? $presets_response['paging']['has_more'] : false,
			]
		] );
	}

	/**
	 * Handles the statistics
	 * @return void
	 */
	public function handle_generate_stats() {
		if ( ! $this->plugin->system()->requests()->check_ajax_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos-pro' ),
			) );
			exit;
		}

		$params = [];

		$author_uplaods_only = (int) $this->plugin->system()->settings()->get( 'admin.videos_list_table.show_author_uploads_only' );

		if ( $author_uplaods_only ) {
			$params['author'] = get_current_user_id();
		}

		$total_uplaoded = $this->plugin->system()->database()->get_total_uploaded_size( $params );

		$byte_formatter = new ByteFormatter();

		$view = $this->plugin->system()->views()->get_view( 'admin/partials/library-stats', array(
			'plugin'         => $this->plugin,
			'total_uploaded' => $byte_formatter->format( $total_uplaoded ),
		) );

		wp_send_json_success( [ 'html' => $view ] );

	}

	/**
	 * Obtian the privacy option based on input and plan support.
	 *
	 * @param $input
	 *
	 * @return mixed|string|null
	 *
	 * @since 1.7.0
	 */
	private function get_view_privacy( $input ) {

		$default = $this->plugin->system()->settings()->get( 'privacy.view_privacy_admin' );
		$privacy = $input === 'default' || empty( $input ) ? $default : $input;
		if ( $this->plugin->system()->vimeo()->supports_view_privacy_option( $privacy ) ) {
			return $privacy;
		} else {
			return 'default';
		}
	}
}