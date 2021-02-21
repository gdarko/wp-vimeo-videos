<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of WP Vimeo Videos
 *
 * WP Vimeo Videos is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * WP Vimeo Videos is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Vimeo Videos. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

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

/**
 * Returns the tmp dir
 * @return string
 */
function wvv_get_tmp_dir() {
    $uploads = wp_upload_dir();
    $base = trailingslashit($uploads['basedir'].DIRECTORY_SEPARATOR);
    $dir = "{$base}wp-vimeo-videos";
    if(is_writable($base)) {
        // Note: If dir already exists it will return TRUE
        if(wp_mkdir_p($dir)) {
            return $dir;
        } else {
            error_log('DGV: Failed to create tmp dir: '.$dir);
        }
    } else {
        error_log('DGV: Base '.$base . ' not writable.');
    }
    return false;
}

/**
 * Return the tmp dir url
 * @return string
 */
function wvv_get_tmp_dir_url() {
    $uploads = wp_upload_dir();
    return $uploads['baseurl'] . '/wp-vimeo-videos';
}


/**
 * The vimeo insert methods in the Gutenberg and TinyMCE editors that are supported.
 * @return array
 */
function wvv_get_editor_insert_methods() {
	return array(
		'upload' => __( 'Upload new Vimeo video', 'wp-vimeo-videos' ),
		'local'  => __( 'Insert Vimeo video from local library', 'wp-vimeo-videos' ),
	);
}
