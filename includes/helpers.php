<?php
/**
 * Query all videos
 *
 * @param $args
 *
 * @return array
 */
function dgv_videos_get( $args ) {
	$posts = get_posts( array(
		'post_type'      => DGV_PT_VU,
		'posts_per_page' => $args['number'],
		'offset'         => $args['offset'],
		'post_status'    => 'publish'
	) );

	return $posts;
}

/**
 * Get total videos count
 * @return int
 */
function dgv_videos_count() {
	$posts = get_posts( array(
		'post_type'      => DGV_PT_VU,
		'posts_per_page' => - 1,
		'post_status'    => 'publish'
	) );

	return count( $posts );
}

/**
 * Returns view, allows to pass data
 *
 * @param $view
 * @param array $data
 *
 * @return false|string
 */
function dgv_get_view( $view, $data = array() ) {
	if ( is_array( $data ) && ! empty( $data ) ) {
		extract( $data );
	}
	$path = apply_filters( 'dgv_view_path', DGV_PATH . 'views/' . $view . '.php', $view, $data );
	if ( file_exists( $path ) ) {
		ob_start();
		include( $path );

		return ob_get_clean();
	} else {
		error_log( __( 'DGV: Template file does not exists.', 'wp-vimeo-videos' ) );

		return '';
	}
}

/**
 * Extract vimeo ID out of the vimeo CREATE/UPLOAD response
 *
 * @param $post_ID
 *
 * @return mixed
 */
function dgv_get_vimeo_id( $post_ID ) {
	$response = get_post_meta( $post_ID, 'dgv_response', true );
	$vimeo_id = str_replace( '/videos/', '', $response );

	return $vimeo_id;
}

/**
 * Returns the vimeo instance
 * @return bool|\Vimeo\Vimeo
 * @throws Exception
 */
function dgv_new_vimeo_instance() {

	$client_id     = get_option( 'dgv_client_id' );
	$client_secret = get_option( 'dgv_client_secret' );
	$access_token  = get_option( 'dgv_access_token' );
	$error         = '';
	if ( empty( $client_id ) || strlen( trim( $client_id ) ) === 0 ) {
		$error = __( 'Client ID is missing', 'wp-vimeo-videos' );
	} else if ( empty( $client_secret ) || strlen( trim( $client_secret ) ) === 0 ) {
		$error = __( 'Client Secret is missing', 'wp-vimeo-videos' );
	} else if ( empty( $access_token ) || strlen( trim( $access_token ) ) === 0 ) {
		$error = __( 'Access Token is missing', 'wp-vimeo-videos' );
	}
	if(!class_exists('\Vimeo\Vimeo')) {
		$error = __('Vimeo not loaded', 'wp-vimeo-videos');
	}
	if ( trim($error) !== '' ) {
		throw new \Exception( $error );
	}
	$vimeo = new \Vimeo\Vimeo( $client_id, $client_secret, $access_token );
	return $vimeo;
}

/**
 * Check if the credentials are valid
 * @return array|bool
 */
function dgv_check_api_connection() {
	try {
		$vimeo = dgv_new_vimeo_instance();
		return $vimeo instanceof \Vimeo\Vimeo;
	} catch (\Exception $e) {
		if(WP_DEBUG) {
			error_log('DGV Error: ' . $e->getMessage());
		}
		return false;
	}
}

/**
 * Upload video
 * @param $file_path
 * @param $params
 *
 * @return string
 * @throws \Vimeo\Exceptions\VimeoRequestException
 * @throws \Vimeo\Exceptions\VimeoUploadException
 * @throws \Exception
 */
function dgv_vimeo_upload( $file_path, $params ) {
	$vimeo = dgv_new_vimeo_instance();
	return $vimeo->upload( $file_path, $params );
}