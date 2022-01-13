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
 * Class WP_DGV_Ajax_Handler
 *
 * Responsible for ajax handling
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV_Ajax_Handler {

    /**
     * The settings api
     * @var WP_DGV_Settings_Helper
     */
    protected $settings_helper;

	/**
	 * The api helper
	 * @var WP_DGV_Api_Helper
	 */
	protected $api_helper;

	/**
	 * The database helper
	 * @var WP_DGV_Db_Helper
	 */
	protected $db_helper;

	/**
	 * The logger
	 * @var WP_DGV_Logger
	 */
	protected $logger;

	/**
	 * WP_DGV_Ajax_Handler constructor.
	 *
	 * @param $plugin_name
	 * @param $plugin_version
	 */
	public function __construct( $plugin_name, $plugin_version ) {
		$this->api_helper = new WP_DGV_Api_Helper();
		$this->db_helper  = new WP_DGV_Db_Helper();
        $this->settings_helper = new WP_DGV_Settings_Helper();
        $this->logger = new WP_DGV_Logger();
    }

	/**
	 * Handles settings storage
	 */
	public function handle_settings() {

		if ( ! $this->check_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos' ),
			) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unauthorized action', 'wp-vimeo-videos' )
			) );
			exit;
		}

		$fields = array(
			'dgv_client_id'       => __( 'Client ID', 'wp-vimeo-videos' ),
			'dgv_client_secret'   => __( 'Client Secret', 'wp-vimeo-videos' ),
			'dgv_access_token'    => __( 'Access Token', 'wp-vimeo-videos' ),
			'dgv_author_uploads_only' => __( 'Show Author uploads only', 'wp-vimeo-videos' ),
		);

		// validate
		foreach ( $fields as $key => $field ) {
			if ( in_array( $key, array( 'dgv_client_id', 'dgv_client_secret', 'dgv_access_token' ) ) ) {
				if ( ! isset( $_POST[ $key ] ) || empty( $_POST[ $key ] ) || empty( trim( $_POST[ $key ] ) ) ) {
					wp_send_json_error( array(
						'message' => sprintf( __( 'Error: %s is required.', 'wp-vimeo-videos' ), $field )
					) );
					exit;
				}
			}
		}

		// save
        foreach ($fields as $key => $field) {
            if ( ! isset($_POST[$key])) {
                $this->settings_helper->remove($key);
            } else {
            	$value = sanitize_text_field($_POST[$key]);
                $option_value = $this->prepare_setting($key, $value);
                $this->settings_helper->set($key, $option_value);
            }
        }

        $this->settings_helper->save();


		// Re-render the api details
		WP_DGV_Api_Helper::flush_cache();
		$this->api_helper = new WP_DGV_Api_Helper();
		$api_info         = wvv_get_view( 'admin/partials/api', array(
			'vimeo_helper' => $this->api_helper
		) );

		wp_send_json_success( array(
			'message'           => __( 'Settings saved successfully!', 'wp-vimeo-videos' ),
			'api_info'          => $api_info,
			'product_key_valid' => false,
		) );
	}

    /**
     * Prepare
     *
     * @param $key
     * @param $value
     *
     * @return array|string
     */
    public function prepare_setting($key, $value) {

        if($key === 'dgv_embed_domains') {
            foreach(array('https://', 'http://', 'www.') as $part) {
                $value = str_replace($part, '', $value);
            }
        }
        return $value;
    }

	/**
	 * Store upload on the server
	 */
	public function store_upload() {

		$logtag = 'DGV-EDITOR-UPLOAD';

		if ( ! $this->check_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos' ),
			) );
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unauthorized action. User must be Author, Editor or Administrator to edit.', 'wp-vimeo-videos' )
			) );
			exit;
		}

		if ( ! $this->is_http_post() ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid request.', 'wp-vimeo-videos' ),
			) );
		}

		$title       = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : __( 'Untitled', 'wp-vimeo-videos' );
		$description = isset( $_POST['description'] ) ? sanitize_text_field( $_POST['description'] ) : '';
		$size        = isset( $_POST['size'] ) ? intval( $_POST['size'] ) : false;
		$uri         = sanitize_text_field( $_POST['uri'] );
		$video_id    = wvv_uri_to_id( $uri );

		/**
		 * Upload success hook
		 */
		do_action( 'dgv_backend_after_upload', array(
			'vimeo_title'       => $title,
			'vimeo_description' => $description,
			'vimeo_id'          => $video_id,
			'vimeo_size'        => $size,
			'source'            => array(
				'software' => 'Editor',
			),
		) );

		$this->logger->log( sprintf( 'Video %s stored.', $uri ), $logtag );

		wp_send_json_success( array(
			'message' => __( 'Video uploaded successfully.', 'wp-vimeo-videos' ),
		) );

		exit;

	}

    /**
     * Handles user search
     */
    public function handle_user_search() {

        if ( ! $this->check_referer('dgvsecurity') ) {
            wp_send_json_error( array(
                'message' => __( 'Security Check Failed.', 'wp-vimeo-videos' ),
            ) );
            exit;
        }

	    $items = array();
	    $phrase = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';

	    $params = array(
		    'number' => 100,
	    );
	    if ( ! empty( $phrase ) ) {
		    $params['search'] = '*' . esc_attr( $phrase ) . '*';
		    $params['search_columns'] = array(
			    'user_login',
			    'user_nicename',
			    'user_email',
			    'user_url',
		    );
	    }
	    $query = new \WP_User_Query( $params );
	    foreach( $query->get_results() as $user ) {
		    $items[] = array(
			    'id'   => $user->ID,
			    'name' => sprintf( '%s (#%d - %s)', $user->user_nicename, $user->ID, $user->user_email )
		    );
	    }

	    wp_send_json_success( $items );
    }


	/**
	 * Return the uploaded videos
	 */
	public function get_uploads() {

		if ( ! $this->check_referer( 'dgvsecurity' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security Check Failed.', 'wp-vimeo-videos' ),
			) );
			exit;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Unauthorized action', 'wp-vimeo-videos' )
			) );
			exit;
		}

		if ( ! $this->is_http_get() ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid request', 'wp-vimeo-videos' )
			) );
			exit;
		}

		$current_user_uploads = ! current_user_can( 'administrator' ) && (int) $this->settings_helper->get( 'dgv_local_current_user_only' );

		$uplaods = $this->db_helper->get_uploaded_videos( $current_user_uploads );

		wp_send_json_success( array(
			'uploads' => $uplaods,
		) );
		exit;

	}

	/**
	 * Utility function to check if the request is GET
	 * @return bool
	 */
	private function is_http_get() {
		return $_SERVER['REQUEST_METHOD'] === 'GET';
	}

	/**
	 * Utility function to check if the request is POST
	 * @return bool
	 */
	private function is_http_post() {
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

	/**
	 * Utility function to chec kif the request is secure
	 * @param $nonce_name
	 *
	 * @return bool|int
	 */
	private function check_referer( $nonce_name ) {
		return check_ajax_referer( $nonce_name, '_wpnonce', false );
	}

}
