<?php

/**
 * The ajax handling functionality
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_DGV
 * @subpackage WP_DGV/admin
 * @copyright     Darko Gjorgjijoski <info@codeverve.com>
 * @license    GPLv2
 */
class WP_DGV_Ajax_Handler {

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
	 * WP_DGV_Ajax_Handler constructor.
	 *
	 * @param $plugin_name
	 * @param $plugin_version
	 */
	public function __construct( $plugin_name, $plugin_version ) {
		$this->api_helper = new WP_DGV_Api_Helper();
		$this->db_helper  = new WP_DGV_Db_Helper();
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
			'dgv_upload_approach' => __( 'Upload Approach', 'wp-vimeo-videos' ),
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
		foreach ( $fields as $key => $field ) {
			if ( in_array( $key, array( 'dgv_client_id', 'dgv_client_secret', 'dgv_access_token' ) ) ) {
				$option_value = sanitize_text_field( $_POST[ $key ] );
			} else {
				$option_value = $_POST[ $key ];
			}
			update_option( $key, $option_value );
		}

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
	 * Store upload on the server
	 */
	public function store_upload() {

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

		$title       = isset( $_POST['title'] ) ? $_POST['title'] : __( 'Untitled', 'wp-vimeo-videos' );
		$description = isset( $_POST['description'] ) ? $_POST['description'] : '';
		$uri         = $_POST['uri'];

		$result = $this->db_helper->create_local_video( $title, $description, $uri );
		if ( ! is_wp_error( $result ) ) {

			do_action( 'dgv_after_upload', $uri, $this->api_helper->api );

			wp_send_json_success( array(
				'message' => __( 'Video uploaded successfully.', 'wp-vimeo-videos' ),
				'post_id' => $result,
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to store the video entry in the local db.', 'wp-vimeo-videos' ),
			) );
		}
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