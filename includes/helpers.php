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