<?php

/**
 * Renders view with data
 *
 * @param $view
 * @param array $data
 *
 * @return false|string
 * @since    1.0.0
 *
 */
function wvv_get_view( $view, $data = array() ) {
	$path = WP_VIMEO_VIDEOS_PATH . $view . '.php';
	if ( file_exists( $path ) ) {
		ob_start();
		if ( ! empty( $data ) ) {
			extract( $data );
		}
		include( $path );

		return ob_get_clean();
	}

	return '';
}

/**
 * Is the vimeo minimum php version satisfied?
 * @return bool
 * @since    1.0.0
 */
function wvv_php_version_ok() {
	return version_compare( PHP_VERSION, WP_VIMEO_VIDEOS_MIN_PHP_VERSION, '>=' );
}

/**
 * Clean up the local files, if the user used PULL method to upload the plugin stores the videos locally and then vimeo downloads those videos.
 * In 24 hours they are deleted assuming vimeo processed and stored those files on their end.
 * @since 1.0.0
 * @retrun void
 */
function wvv_cleanup_local_files() {
	$args   = array(
		'post_type'      => 'dgv-upload',
		'posts_per_page' => - 1,
		'meta_query'     => array(
			array(
				'key'     => 'dgv_local_file',
				'value'   => '',
				'compare' => '!='
			)
		),
		'date_query'     => array(
			array(
				'before'    => '24 hours ago',
				'inclusive' => true,
			),
		),
	);
	$videos = get_posts( $args );
	foreach ( $videos as $video ) {
		$local_path = get_post_meta( $video->ID, 'dgv_local_file', true );
		if ( ! empty( $local_path ) ) {
			if ( file_exists( $local_path ) ) {
				@unlink( $local_path );
				delete_post_meta( $video->ID, 'dgv_local_file' );
			}
		}
	}
}

/**
 * Check if the block editor is active.
 * @return bool
 */
function wvv_is_gutenberg_active() {
	if ( function_exists( 'is_gutenberg_page' ) &&
	     is_gutenberg_page()
	) {
		// The Gutenberg plugin is on.
		return true;
	}

	require_once(ABSPATH . 'wp-admin/includes/screen.php');

	$current_screen = get_current_screen();
	if ( method_exists( $current_screen, 'is_block_editor' ) &&
	     $current_screen->is_block_editor()
	) {
		// Gutenberg page on 5+.
		return true;
	}
	return false;
}

/**
 * Return the guide url
 * @return string
 */
function wvv_get_guide_url() {
	return 'https://bit.ly/wvvdocs';
}

/**
 * Return the purchase url
 * @return string
 */
function wvv_get_purchase_url() {
	return 'http://bit.ly/wvvpurchase';
}

/**
 * Format bytes
 *
 * @param $bytes
 * @param int $precision
 *
 * @return string
 */
function wvv_format_bytes( $bytes, $precision = 4 ) {

	$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

	$bytes = max( $bytes, 0 );
	$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
	$pow   = min( $pow, count( $units ) - 1 );

	// Uncomment one of the following alternatives
	//$bytes /= pow( 1024, $pow );

	$bytes /= (1 << (10 * $pow));

	return round( $bytes, $precision ) . ' ' . $units[ $pow ];
}

/**
 * Convert Vimeo URI to ID
 *
 * @param $uri
 *
 * @return mixed
 */
function wvv_uri_to_id( $uri ) {
	$parts = explode( '/', $uri );

	return end( $parts );
}

/**
 * Convert Response to URI
 *  -- Support for pull method which returns array structure ['body']['uri']
 *  -- Support for upload stream method which returns the uri directly.
 *
 * @param $response
 *
 * @return string
 */
function wvv_response_to_uri( $response ) {
	$uri = '';
	if ( isset( $response['body']['uri'] ) ) { // Support for pull method
		$uri = $response['body']['uri'];
	} else {
		if ( is_string( $response ) ) { // Support for upload method.
			$video_id = wvv_uri_to_id( $response );
			if ( is_numeric( $video_id ) ) {
				$uri = $response;
			}
		}
	}

	return $uri;
}