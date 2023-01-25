<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of Video Uploads for Vimeo PRO
 *
 * Video Uploads for Vimeo PRO is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Video Uploads for Vimeo PRO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Video Uploads for Vimeo PRO. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_API_Base
 *
 * Responsible for communicating with the Vimeo API, shared methods, used in both free and pro version.
 *
 * @note The only difference is the textdomain, otherwise the code is shared in both versions.
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV_API_Base {

	// Plans
	const PLAN_BASIC = 'basic';
	const PLAN_PLUS = 'plus';
	const PLAN_PRO = 'pro';
	const PLAN_PRO_UNLIMITED = 'pro_unlimited';
	const PLAN_BUSINESS = 'business';
	const PLAN_PREMIUM = 'premium';
	const PLAN_PRODUCER = 'producer';
	const PLAN_LIVE_PREMIUM = 'live_premium';
	const PLAN_LIVE_BUSINESS = 'live_business';
	const PLAN_LIVE_PRO = 'live_pro';

	/**
	 * Is the api connected?
	 *
	 * @since 1.0.0
	 *
	 * @var null
	 */
	public $is_connected = false;

	/**
	 * Is authenticated connection?
	 *
	 * Authenticated connections are required.
	 *
	 * @since 1.5.0
	 *
	 * @var bool
	 */
	public $is_authenticated_connection = true;


	/**
	 * Return the vimeo instance
	 *
	 * @since 1.0.0
	 *
	 * @var null|\Vimeo\Vimeo
	 */
	public $api = null;

	/**
	 * List of the required scopes
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $scopes_required = array(
		'create',
		'interact',
		'video_files',
		'private',
		'edit',
		'upload',
		'delete'
	);

	/**
	 * List of the missing scopes
	 * @var array
	 */
	public $scopes_missing = array();

	/**
	 * List of scopes tied to the authenticated user
	 * @var array
	 */
	public $scopes = array();

	/**
	 * The name of the user
	 * @var string
	 */
	public $user_name = '';

	/**
	 * The vimeo user uri
	 * @var string
	 */
	public $user_uri = '';

	/**
	 * The vimeo user link
	 * @var string
	 */
	public $user_link = '';

	/**
	 * Returns the user type
	 * @var string
	 */
	public $user_type = '';

	/**
	 * The upload quota
	 * @var array
	 */
	public $upload_quota = [];

	/**
	 * The headers
	 * @var array
	 */
	public $headers = [];

	/**
	 * The oAuth APP name
	 * @var string
	 */
	public $app_name = '';

	/**
	 * The oAuth APP URI
	 * @var string
	 */
	public $app_uri = '';

	/**
	 * Keeps the error if any.
	 * @var null
	 */
	public $error = null;

	/**
	 * The settings helper
	 * @var WP_DGV_Settings_Helper
	 */
	public $settings_helper;

	/**
	 * Cache time for the vimeo user data
	 * @var int
	 */
	public $cache_time = 120;

	/**
	 * Cache key name when caching the vimeo user data
	 * @var string
	 */
	const CACHE_KEY = 'wvv_account_data';

	/**
	 * WP_DGV_Api_Helper constructor.
	 */
	public function __construct() {

		$this->settings_helper = new WP_DGV_Settings_Helper();

		$this->connect();
	}

	/**
	 * Connect to vimeo
	 *
	 * @param  bool  $flush_cache
	 *
	 * @since 1.0.0
	 *
	 */
	public function connect( $flush_cache = false ) {

		$client_id     = $this->settings_helper->get( 'dgv_client_id', '' );
		$client_secret = $this->settings_helper->get( 'dgv_client_secret', '' );
		$access_token  = $this->settings_helper->get( 'dgv_access_token', '' );

		$error = null;

		if ( empty( $client_id ) || strlen( trim( $client_id ) ) === 0 ) {
			$error = __( 'Client ID is missing', 'wp-vimeo-videos' );
		} elseif ( empty( $client_secret ) || strlen( trim( $client_secret ) ) === 0 ) {
			$error = __( 'Client Secret is missing', 'wp-vimeo-videos' );
		} elseif ( empty( $access_token ) || strlen( trim( $access_token ) ) === 0 ) {
			$error = __( 'Access Token is missing', 'wp-vimeo-videos' );
		}

		if ( ! class_exists( '\Vimeo\Vimeo' ) ) {
			$error = __( 'Vimeo not loaded', 'wp-vimeo-videos' );
		}

		$this->error = $error;

		if ( is_null( $this->error ) ) {

			$this->api = new WP_DGV_Vimeo( $client_id, $client_secret, $access_token );

			// Maybe flush cache?
			if ( $flush_cache ) {
				self::flush_cache();
			}

			// Cache the data
			$data = get_transient( self::CACHE_KEY );
			if ( false === $data ) {
				try {
					$data = $this->verify_connection();
					set_transient( self::CACHE_KEY, $data, $this->cache_time );
				} catch ( \Exception $e ) {
					$data        = null;
					$this->error = $e->getMessage();
				}
			}

			// Verify the connection
			if ( ! is_null( $data ) && is_array( $data ) && isset( $data['status'] ) ) {
				$status = $data['status'];
				if ( $status === 200 ) {
					$this->is_connected = true;
					// If user object is not present assume this is unauthenticated connection.
					if ( ! isset( $data['body']['user'] ) ) {
						$this->is_authenticated_connection = false;
					}
				} else {
					$this->is_connected                = false;
					$this->is_authenticated_connection = false;
					if ( isset( $data['body']['developer_message'] ) ) {
						$this->error = $data['body']['developer_message'];
					}
				}
			} else {
				// Error is set in exception method.
				$this->is_connected = false;
			}
		}

		if ( $this->is_connected ) {

			$this->user_name    = isset( $data['body']['user']['name'] ) ? $data['body']['user']['name'] : '';
			$this->user_uri     = isset( $data['body']['user']['uri'] ) ? $data['body']['user']['uri'] : '';
			$this->user_link    = isset( $data['body']['user']['link'] ) ? $data['body']['user']['link'] : '';
			$this->user_type    = isset( $data['body']['user']['account'] ) ? $data['body']['user']['account'] : '';
			$this->app_name     = isset( $data['body']['app']['name'] ) ? $data['body']['app']['name'] : '';
			$this->app_uri      = isset( $data['body']['app']['uri'] ) ? $data['body']['app']['uri'] : '';
			$this->headers      = isset( $data['headers'] ) ? $data['headers'] : array();
			$this->upload_quota = isset( $data['body']['user']['upload_quota'] ) ? $data['body']['user']['upload_quota'] : array();
			$_scopes            = isset( $data['body']['scope'] ) ? $data['body']['scope'] : '';

			if ( ! empty( $_scopes ) ) {
				$this->scopes         = explode( ' ', $_scopes );
				$this->scopes_missing = array_diff( $this->scopes_required, $this->scopes );
			}
		} else {
			$logger = new WP_DGV_Logger();
			$logtag = 'DGV-VIMEO-CONNECTION';
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$logger->log( sprintf( 'Error connecting to Vimeo: %s', $this->error ), $logtag );
			}
		}
	}

	/**
	 * Used to detect problems with active connection
	 *
	 * @return array
	 * @since 1.5.0
	 *
	 */
	public function find_problems() {
		$problems = array();

		// Check connection, if wrong bail immediately.
		if ( ! $this->is_authenticated_connection ) {
			array_push( $problems, array(
				'code' => 'unauthenticated',
				'info' => __( 'Your Access Token is of type "Unauthenticated". This will prevent normal operation of the plugin.', 'wp-vimeo-videos' ),
				"fix"  => sprintf( __( 'To fix the issue, go to Vimeo Developer Portal, select your application and remove your old Access Token. Generate new "Auhtneticated" Access Token and select the %s scopes. Once done, set the new Access Token in the Settings screen and Purge Cache.', 'wp-vimeo-videos' ), implode( ', ', $this->scopes_required ) )
			) );

			return $problems;
		}

		// Continue with scopes.
		if ( $this->is_connected ) {
			if ( ! $this->can_upload() ) {
				array_push( $problems, array(
					'code' => 'cant_upload',
					'info' => __( 'Your Access Token is missing "Upload" scope. This will prevent uploading new Videos to Vimeo.', 'wp-vimeo-videos' ),
					"fix"  => sprintf( __( 'To fix the issue, go to Vimeo Developer Portal, select your application and remove your old Access Token. Generate new "Auhtneticated" Access Token and select the %s scopes. Once done, set the new Access Token in the Settings screen and Purge Cache.', 'wp-vimeo-videos' ), implode( ', ', $this->scopes_required ) )
				) );
			}
			if ( ! $this->can_edit() ) {
				array_push( $problems, array(
					'code' => 'cant_edit',
					'info' => __( 'Your Access Token is missing "Edit" scope. This will prevent editing Videos from the edit screen.', 'wp-vimeo-videos' ),
					"fix"  => sprintf( __( 'To fix the issue, go to Vimeo Developer Portal, select your application and remove your old Access Token. Generate new "Auhtneticated" Access Token and select the %s scopes. Once done, set the new Access Token in the Settings screen and Purge Cache.', 'wp-vimeo-videos' ), implode( ', ', $this->scopes_required ) )
				) );
			}
			if ( ! $this->can_delete() ) {
				array_push( $problems, array(
					'code' => 'cant_delete',
					'info' => __( 'Your Access Token is missing "Delete" scope. This will prevent deleting Videos from the admin dashboard.' ),
					"fix"  => sprintf( __( 'To fix the issue, go to Vimeo Developer Portal, select your application and remove your old Access Token. Generate new "Auhtneticated" Access Token and select the %s scopes. Once done, set the new Access Token in the Settings screen and Purge Cache.', 'wp-vimeo-videos' ), implode( ', ', $this->scopes_required ) )
				) );
			}
		}

		if ( ! $this->supports_folders() ) {
			array_push( $problems, array(
				'code' => 'cant_use_folders',
				'info' => __( 'Your Access Token is missing "Interact" scope. This will prevent using the Folders feature in the Video edit screen.' ),
				"fix"  => sprintf( __( 'To fix the issue, go to Vimeo Developer Portal, select your application and remove your old Access Token. Generate new "Auhtneticated" Access Token and select the %s scopes. Once done, set the new Access Token in the Settings screen and Purge Cache.', 'wp-vimeo-videos' ), implode( ', ', $this->scopes_required ) )
			) );
		}

		$max_exec_time = ini_get( 'max_execution_time' );
		if ( $max_exec_time > 0 && $max_exec_time < 240 ) {
			array_push( $problems, array(
				'code' => 'exec_time_low',
				'info' => sprintf( __( 'Your <strong>max_exuction_time</strong> configuration is %s seconds which is very low. Larger uploads that exceed %s seconds for uploading will be dropped by the system and you may see "Uploading..." forever in Vimeo.' ), $max_exec_time, $max_exec_time ),
				"fix"  => sprintf( __( 'To fix the issue find your php.ini and increase max_exuction_time value, if you use cPanel find PHP Settings or if you can\'t find anything contact your hosting provider.', 'wp-vimeo-videos' ) )
			) );
		}

		$max_input_time = ini_get( 'max_input_time' );
		if ( $max_input_time > 0 && $max_input_time < 240 ) {
			array_push( $problems, array(
				'code' => 'input_time_low',
				'info' => sprintf( __( 'Your <strong>max_input_time</strong> configuration is %s seconds which is very low. The client connection will be dropped after %s seconds from initating the upload. This is especially required for people with slow connection as it takes more seconds to upload a file.' ), $max_input_time, $max_input_time ),
				"fix"  => sprintf( __( 'To fix the issue find your php.ini and increase max_input_time value, if you use cPanel find PHP Settings or if you can\'t find anything contact your hosting provider.', 'wp-vimeo-videos' ) )
			) );
		}

		return $problems;
	}

	/**
	 * Is basic/free account?
	 *
	 * @return bool
	 * @since 1.1.0
	 *
	 */
	public function is_free() {
		return $this->user_type === self::PLAN_BASIC;
	}

	/**
	 * Is basic/free account (alias of is_free())
	 *
	 * @return bool
	 * @since 1.5.0
	 *
	 */
	public function is_basic_plan() {
		return $this->is_free();
	}

	/**
	 * Is plus account?
	 *
	 * @return bool
	 * @since 1.5.0
	 *
	 */
	public function is_plus_plan() {
		return $this->user_type === self::PLAN_PLUS;
	}

	/**
	 * Is pro account?
	 *
	 * @return bool
	 * @since 1.5.0
	 *
	 */
	public function is_pro_plan() {
		return $this->user_type === self::PLAN_PRO;
	}

	/**
	 * Is pro unlimited plan?
	 * @return bool
	 * @since 1.5.1
	 */
	public function is_pro_unlimited_plan() {
		return $this->user_type === self::PLAN_PRO_UNLIMITED;
	}

	/**
	 * Is pro account?
	 *
	 * @return bool
	 * @since 1.5.0
	 *
	 */
	public function is_business_plan() {
		return $this->user_type === self::PLAN_BUSINESS;
	}

	/**
	 * Is premium plan?
	 *
	 * @return bool
	 * @since 1.5.0
	 *
	 */
	public function is_premium_plan() {
		return $this->user_type === self::PLAN_PREMIUM;
	}

	/**
	 * Is premium plan?
	 *
	 * @return bool
	 * @since 1.5.0
	 *
	 */
	public function is_live_premium_plan() {
		return $this->user_type === self::PLAN_LIVE_PREMIUM;
	}

	/**
	 * Is live pro plan?
	 * @return bool
	 */
	public function is_live_pro_plan() {
		return $this->user_type === self::PLAN_LIVE_PRO;
	}

	/**
	 * Is live business plan?
	 * @return string
	 */
	public function is_live_business_plan() {
		return $this->user_type === self::PLAN_LIVE_BUSINESS;
	}

	/**
	 * Is producer plan?
	 * @return bool
	 */
	public function is_producer_plan() {
		return $this->user_type === self::PLAN_PRODUCER;
	}

	/**
	 * Is ANY paid plan?
	 *
	 * @return bool
	 * @since 1.5.0
	 *
	 */
	public function is_paid_plan() {
		return $this->user_type !== self::PLAN_BASIC;
	}

	/**
	 * Check if the user can interact
	 * @return bool
	 */
	public function can_interact() {
		return in_array( 'interact', $this->scopes );
	}

	/**
	 * Check if the current authenticated user can create.
	 * @return bool
	 */
	public function can_create() {
		return in_array( 'create', $this->scopes );
	}

	/**
	 * Check if the current authenticated user can edit.
	 * @return bool
	 */
	public function can_edit() {
		return in_array( 'edit', $this->scopes );
	}

	/**
	 * Check if the current authenticated user can upload.
	 * @return bool
	 */
	public function can_upload() {
		return in_array( 'upload', $this->scopes );
	}

	/**
	 * Check if the current authenticated user can delete.
	 * @return bool
	 */
	public function can_delete() {
		return in_array( 'delete', $this->scopes );
	}

	/**
	 * Check if the current authenticated user supports embed prviacy
	 *
	 * @return bool
	 * @since 1.5.0
	 *
	 */
	public function supports_embed_privacy() {
		return $this->is_paid_plan();
	}

	/**
	 * Check if the current authenticated user supports embed prsets
	 *
	 * @return bool
	 * @since 1.5.0
	 *
	 */
	public function supports_embed_presets() {
		return $this->is_pro_plan()
		       || $this->is_pro_unlimited_plan()
		       || $this->is_live_pro_plan()
		       || $this->is_business_plan()
		       || $this->is_live_business_plan()
		       || $this->is_premium_plan()
		       || $this->is_live_premium_plan()
		       || $this->is_producer_plan();
	}

	/**
	 * Check if the current authenticated user supports folder
	 * @since 1.7.0
	 */
	public function supports_folders() {
		return $this->can_interact();
	}

	/**
	 * Check if the current authenticated user can use the 'Disable' option of privacy.view
	 * @link https://vimeo.zendesk.com/hc/en-us/articles/224817847-Privacy-settings-overview
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function supports_view_privacy_option_disable() {
		return $this->is_paid_plan();
	}

	/**
	 * Check if the current authenticated user can use the 'Unlisted' option of privacy.view
	 * @link https://vimeo.zendesk.com/hc/en-us/articles/224817847-Privacy-settings-overview
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function supports_view_privacy_option_unlisted() {
		return $this->is_paid_plan();
	}

	/**
	 * Return list of videos
	 * @url https://developer.vimeo.com/api/reference/videos#get_videos
	 *
	 * @param  array  $params
	 * @param  bool  $try_all
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 */
	public function get_uploaded_videos( $params = array(), $try_all = false ) {
		$result = $this->get_uploaded_videos_safe( $params, $try_all ? true : false );

		return isset( $result['videos'] ) ? $result['videos'] : [];
	}

	/**
	 * Attempt to get as much as possible videos from the Vimeo.com API without hitting the rate limits.
	 *
	 * @param  array  $params
	 * @param  bool  $recursive
	 * @param  int  $calls_buffer
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 */
	public function get_uploaded_videos_safe( $params = [], $recursive = true, $calls_buffer = 3 ) {

		$result   = array(
			'videos'          => [],
			'total_pages'     => 0,
			'latest_page'     => 0,
			'calls_remaining' => 0,
			'no_calls'        => false,
		);
		$defaults = array(
			'fields'            => 'uri,name,description',
			'filter'            => 'embeddable',
			'filter_embeddable' => true,
			'per_page'          => 100,
			'page'              => 1,
		);

		$query = wp_parse_args( $params, $defaults );

		$response = $this->api->request( '/me/videos', $query, 'GET' );
		$response = $this->prepare_response( $response );
		if ( isset( $response['status'] ) && $response['status'] === 200 ) {

			$result['videos']      = array_merge( $result['videos'], $response['body']['data'] );
			$result['total_pages'] = isset( $response['body']['total'] )
			                         && $response['body']['total'] > 0
			                         && isset( $response['body']['per_page'] )
			                         && $response['body']['per_page'] > 0
			                         && $response['body']['total'] > $response['body']['per_page']
				? (int) ceil( ( $response['body']['total'] / $response['body']['per_page'] ) ) : 1;


			$calls_remaining = isset( $response['headers']['x-ratelimit-remaining'] ) ? $response['headers']['x-ratelimit-remaining'] : 0;

			$result['latest_page']     = $query['page'];
			$result['calls_remaining'] = $calls_remaining;


			if ( $recursive ) {

				$is_out_of_calls = $calls_remaining <= $calls_buffer;

				if ( ! $is_out_of_calls ) {
					if ( $query['page'] < $result['total_pages'] ) { // keep 3 as a buffer space.
						$query['page'] = $query['page'] + 1;
						$new_result    = $this->get_uploaded_videos_safe( $query, $recursive );
						if ( ! empty( $new_result ) ) {
							$result           = array_merge( $result, $new_result );
							$result['videos'] = array_merge( $result['videos'], $new_result['videos'] );
							//$result['latest_page'] = $result['latest_page'] + $new_result['latest_page'];
						}
					}
				} else {
					$result['no_calls'] = true;
				}
			}

		}

		return $result;
	}

	/**
	 * Uploads/streams the video to vimeo
	 *
	 * @param $file_path
	 * @param  array  $params
	 * @param  bool  $process_after_hook  (Processing the after hook will make sure the local video is created and other settings processed like, folders, privacy, etc)
	 *
	 * @return array
	 *
	 * Example Response:
	 *
	 * Array(
	 *    [params] = > (...)
	 *      [response] => /videos/385731411
	 * )
	 *
	 *
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @throws \Vimeo\Exceptions\VimeoUploadException
	 * @since 1.0.0
	 */
	public function upload( $file_path, $params, $process_after_hook = false ) {

		$params = apply_filters( 'dgv_before_create_api_video_params', $params, $file_path, null );

		$response = $this->api->upload( $file_path, $params );
		$response = $this->prepare_response( $response );

		if ( $process_after_hook ) {

			$file_size = file_exists( $file_path ) ? filesize( $file_path ) : false;

			/**
			 * Upload success hook
			 */
			do_action( 'dgv_backend_after_upload', array(
				'vimeo_title'       => isset( $params['name'] ) ? $params['name'] : '',
				'vimeo_description' => isset( $params['description'] ) ? $params['description'] : '',
				'vimeo_id'          => wvv_uri_to_id( $response ),
				'vimeo_size'        => $file_size,
				'source'            => array(
					'software' => 'API::upload',
				),
			) );
		}

		return array(
			'params'   => $params,
			'response' => $response,
		);
	}

	/**
	 * Upload via pull method. Only url to the file is required.
	 *
	 * @param  string  $file_url
	 * @param  array  $params
	 * @param  bool  $process_after_hook  (Processing the after hook will make sure the local video is created and other settings processed like, folders, privacy, etc)
	 * @param  null  $additional_data  (Eg. file_path - Provide if you want the vimeo cron to delete the file after it is pulled)
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @since 1.1.0
	 */
	public function upload_pull( $file_url, $params, $process_after_hook = false, $additional_data = array() ) {

		$params = array_merge_recursive( array( 'upload' => array( 'approach' => 'pull', 'link' => $file_url ) ), $params );
		$params = apply_filters( 'dgv_before_create_api_video_params', $params, null, $file_url );

		$response = $this->api->request( '/me/videos', $params, 'POST' );
		$response = $this->prepare_response( $response );

		if ( $process_after_hook ) {
			/**
			 * Upload success hook
			 */
			do_action( 'dgv_backend_after_upload', array(
				'vimeo_title'       => isset( $params['name'] ) ? $params['name'] : '',
				'vimeo_description' => isset( $params['description'] ) ? $params['description'] : '',
				'vimeo_id'          => wvv_uri_to_id( $response ),
				'vimeo_size'        => false,
				'source'            => array(
					'software' => 'API::upload_pull',
				),
			) );
		}

		/**
		 * Save the pull path for deletion later.
		 */
		if ( ! empty( $additional_data['file_path'] ) ) {
			$this->settings_helper->mark_as_temporary_file( $additional_data['file_path'] );
		}

		return array(
			'params'   => $params,
			'response' => $response
		);
	}

	/**
	 * Get video by uri
	 *
	 * @param $uri
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @since 1.0.0
	 *
	 */
	public function get( $uri ) {
		$response = $this->api->request( $uri, [], 'GET' );

		return $this->prepare_response( $response );
	}

	/**
	 * Deletes vimeo video from their api
	 *
	 * @param $uri
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 *
	 * @since 1.1.0
	 *
	 */
	public function delete( $uri ) {
		$response = $this->api->request( $uri, [], 'DELETE' );

		return $this->prepare_response( $response );
	}

	/**
	 * Set the embed privacy
	 *
	 * @param $uri
	 * @param $privacy
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @since 1.3.0
	 *
	 */
	public function set_embed_privacy( $uri, $privacy ) {
		$response = $this->api->request( $uri, array(
			'privacy' => array(
				'embed' => $privacy
			)
		), 'PATCH' );

		return $this->prepare_response( $response );
	}

	/**
	 * Returns whitelisted domains
	 *
	 * @param $uri
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @since 1.3.0
	 *
	 */
	public function get_whitelisted_domains( $uri ) {
		$request_uri = "{$uri}/privacy/domains";
		$response    = $this->api->request( $request_uri, [], 'GET' );

		return $this->prepare_response( $response );
	}

	/**
	 * Add domain to embed whitelist for specific video
	 *
	 * @param $uri
	 * @param $domain
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @since 1.3.0
	 *
	 */
	public function whitelist_domain_add( $uri, $domain ) {
		$request_uri = "{$uri}/privacy/domains/{$domain}";
		$response    = $this->api->request( $request_uri, [], 'PUT' );

		return $this->prepare_response( $response );
	}

	/**
	 * Remove domain from embed whitelist for specific video
	 *
	 * @param $uri
	 * @param $domain
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @since 1.3.0
	 *
	 */
	public function whitelist_domain_remove( $uri, $domain ) {
		$request_uri = "{$uri}/privacy/domains/{$domain}";
		$response    = $this->api->request( $request_uri, [], 'DELETE' );

		return $this->prepare_response( $response );
	}

	/**
	 * Returns formatted array of available view privacy options for upload
	 *
	 * @param  string  $context
	 *
	 * @return array
	 * @since 1.5.0
	 *
	 */
	public function get_view_privacy_options_for_forms( $context = 'admin' ) {
		$default_privacy_key = $context === 'admin' ? 'dgv_view_privacy' : 'dgv_view_privacy_fe';
		$default_privacy     = $this->settings_helper->get( $default_privacy_key );
		$default_privacy     = $this->supports_view_privacy_option( $default_privacy ) ? $default_privacy : 'anybody';
		$all_options         = $this->get_view_privacy_options();
		$options             = array();
		foreach ( $all_options as $key => $option ) {
			$is_default      = $key === $default_privacy;
			$name            = $is_default ? $option['name'] . ' ' . '(' . __( 'Default' ) . ')' : $option['name'];
			$options[ $key ] = array( 'name' => $name, 'available' => $option['available'], 'default' => $is_default );
		}

		return $options;
	}

	/**
	 * Check if view privacy option is supported.
	 * @link https://vimeo.zendesk.com/hc/en-us/articles/224817847-Privacy-settings-overview
	 *
	 * @since 1.5.0
	 *
	 * @param $option
	 *
	 * @return bool
	 */
	public function supports_view_privacy_option( $option ) {
		if ( $option === 'disable' ) {
			return $this->supports_view_privacy_option_disable();
		} elseif ( $option === 'unlisted' ) {
			return $this->supports_view_privacy_option_unlisted();
		}

		return true;
	}

	/**
	 * Get the available privacy options
	 *
	 * @return array[]
	 * @since 1.5.0
	 *
	 */
	public function get_view_privacy_options() {

		if ( $this->user_type === self::PLAN_BASIC ) {
			$unsupported = ' / ' . $this->get_unavailable_text( self::PLAN_BASIC );
		} else {
			$unsupported = '';
		}

		return array(
			'anybody'  => array(
				'name'      => __( 'Anybody', 'wp-vimeo-videos' ),
				'available' => true
			),
			'disable'  => array(
				'name'      => trim( sprintf( __( 'No one on vimeo.com site %s', 'wp-vimeo-videos' ), $unsupported ) ),
				'available' => $this->supports_view_privacy_option_disable()
			),
			'nobody'   => array(
				'name'      => __( 'Just you', 'wp-vimeo-videos' ),
				'available' => true
			),
			'unlisted' => array(
				'name'      => trim( sprintf( __( 'Only those with link %s', 'wp-vimeo-videos' ), $unsupported ) ),
				'available' => $this->supports_view_privacy_option_unlisted()
			),
			'contacts' => array(
				'name'      => __( 'Vimeo Followers', 'wp-vimeo-videos' ),
				'available' => true
			),
			'users'    => array(
				'name'      => __( 'Vimeo Members', 'wp-vimeo-videos' ),
				'available' => true
			),
		);
	}

	/**
	 * The unavailable text
	 *
	 * @param  null  $plan
	 *
	 * @return string
	 * @since 1.5.0
	 *
	 */
	public function get_unavailable_text( $plan = null ) {
		$plan = is_null( $plan ) ? $this->user_type : $plan;

		return sprintf( __( 'Not supported on %s', 'wp-vimeo-videos' ), 'Vimeo ' . ucfirst( $plan ) );
	}

	/**
	 * Set additional metadata for the video, if response is not provided then obtain it.
	 *
	 * @param $post_id  - local video id
	 * @param $response  - Response data from /videos/{video_id} endpoint(s), need to be supplied.
	 *
	 * @return void
	 *
	 * @since 1.7.0
	 */
	public function set_video_metadata( $post_id, $response = null ) {
		// Check existing response?
		if ( is_null( $response ) ) {
			$db       = new WP_DGV_Db_Helper();
			$vimeo_id = $db->get_vimeo_id( $post_id );
			try {
				$response = $this->get( "/videos/{$vimeo_id}?fields=upload" );
			} catch ( \Exception $e ) {
			}
		}
		if ( is_null( $response ) ) {
			return;
		}

		$api_result = isset( $response['body'] ) ? $response['body'] : '';
		if ( empty( $api_result ) ) {
			return;
		}
		$this->set_local_video_metadata( $post_id, $api_result );
	}

	/**
	 * Set additional metadata for the video.
	 *
	 * @param $post_id  - local video id
	 * @param $api_result  - Response data from /videos/{video_id} endpoint(s), $response['body'] need to be supplied.
	 *
	 * @return void
	 *
	 * @since 1.9.2
	 */
	public function set_local_video_metadata( $post_id, $api_result ) {
		$metadata = $this->generate_video_metadata( $api_result );
		$db       = new WP_DGV_Db_Helper();
		$db->set_metadata( $post_id, $metadata );
	}

	/**
	 * Single video from the API results.
	 *
	 * @param $api_result
	 *
	 * @return array
	 *
	 * @since 1.9.2
	 */
	public function generate_video_metadata( $api_result ) {

		$metadata = [];

		// Find size
		$size = isset( $api_result['upload']['size'] ) ? $api_result['body']['upload']['size'] : '';
		if ( ! empty( $size ) ) {
			$metadata['dgv_size'] = $size;
		}

		// Find duration
		$duration = isset( $api_result['duration'] ) ? $api_result['duration'] : '';
		if ( ! empty( $duration ) ) {
			$metadata['dgv_duration'] = $duration;
		}

		// Find dimensions
		$width  = isset( $api_result['width'] ) ? $api_result['width'] : '';
		$height = isset( $api_result['height'] ) ? $api_result['height'] : '';
		if ( ! empty( $width ) ) {
			$metadata['dgv_width'] = $width;
		}
		if ( ! empty( $height ) ) {
			$metadata['dgv_height'] = $height;
		}

		// Check playability
		$is_playable = isset( $api_result['is_playable'] ) ? $api_result['is_playable'] : null;
		if ( ! is_null( $is_playable ) ) {
			$metadata['dgv_playable'] = $is_playable;
		}

		// Set link
		$link = isset( $api_result['link'] ) && ! empty( $api_result['link'] ) ? $api_result['link'] : null;
		if ( ! is_null( $link ) ) {
			$metadata['dgv_link'] = $link;
		}

		// Set embed link
		$player_embed_url = isset( $api_result['player_embed_url'] ) && ! empty( $api_result['player_embed_url'] ) ? $api_result['player_embed_url'] : null;
		if ( ! is_null( $player_embed_url ) ) {
			$metadata['dgv_embed_link'] = $api_result['player_embed_url'];
		}

		// Set pictures data
		$pictures = isset( $api_result['pictures'] ) && ! empty( $api_result['pictures'] ) ? $api_result['pictures'] : null;
		if ( ! is_null( $pictures ) ) {
			$metadata['pictures'] = $api_result['pictures'];
		}

		return $metadata;
	}

	/**
	 * Verify the vimeo connection
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 */
	public function verify_connection() {
		$response = $this->api->request( '/oauth/verify', [], 'GET' );

		return $this->prepare_response( $response );
	}

	/**
	 * Prepare the response data from Vimeo libraries.
	 */
	public function prepare_response( $response ) {

		/**
		 * Make response headers all lowercase to comply with HTTPv2
		 */
		$headers = isset( $response['headers'] ) ? $response['headers'] : array();
		if ( ! empty( $headers ) ) {
			$response['headers'] = array();
			foreach ( $headers as $key => $value ) {
				$response['headers'][ strtolower( $key ) ] = $value; // Support for HTTPv2
			}
		}

		return $response;
	}

	/**
	 * Flushes user data cache
	 *
	 * @since 1.3.0
	 */
	public static function flush_cache() {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Retrieve video from vimeo API.
	 *
	 * @param $id
	 * @param  array  $fields
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @since 1.7.0
	 *
	 */
	public function get_video_by_local_id( $id, $fields = array() ) {
		$db       = new WP_DGV_Db_Helper();
		$vimeo_id = $db->get_vimeo_id( $id );
		$response = $this->get_video_by_id( $vimeo_id );
		if ( is_array( $response ) && isset( $response['body'] ) ) {
			$this->set_local_video_metadata( $id, $response['body'] );
		}

		return $response;
	}

	/**
	 * Returns video form the api
	 *
	 * @param $id
	 * @param  array  $fields
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 */
	public function get_video_by_id( $id, $fields = array() ) {
		$fields_s = ! empty( $fields ) ? sprintf( '?fields=%s', implode( ',', $fields ) ) : '';
		$full_uri = sprintf( '/videos/%s%s', $id, $fields_s );

		$response = $this->get( $full_uri );

		return $this->prepare_response( $response );
	}

	/**
	 * Verify the calls quota
	 * @return array
	 * @since 1.9.2
	 */
	public function get_calls_quota( $fresh = false ) {
		$data = [
			'limit'     => isset( $this->headers['x-ratelimit-limit'] ) ? $this->headers['x-ratelimit-limit'] : null,
			'remaining' => isset( $this->headers['x-ratelimit-remaining'] ) ? $this->headers['x-ratelimit-remaining'] : null,
			'reset'     => isset( $this->headers['x-ratelimit-reset'] ) ? $this->headers['x-ratelimit-reset'] : null,
		];
		if ( $fresh ) {
			try {
				$connection = $this->verify_connection();
				if ( isset( $connection['headers'] ) ) {
					$data = [
						'limit'     => isset( $connection['headers']['x-ratelimit-limit'] ) ? $connection['headers']['x-ratelimit-limit'] : null,
						'remaining' => isset( $connection['headers']['x-ratelimit-remaining'] ) ? $connection['headers']['x-ratelimit-remaining'] : null,
						'reset'     => isset( $connection['headers']['x-ratelimit-reset'] ) ? $connection['headers']['x-ratelimit-reset'] : null,
					];
				}
			} catch ( \Exception $e ) {

			}
		}

		return $data;
	}
}