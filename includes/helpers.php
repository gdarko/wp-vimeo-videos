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
	return 'https://docs.codeverve.com/video-uploads-for-vimeo';
}

/**
 * Return the purchase url
 * @return string
 */
function wvv_get_purchase_url() {
	return 'https://codeverve.com/video-uploads-for-vimeo';
}

/**
 * String contains
 *
 * @param $str
 * @param $substr
 *
 * @return bool
 * @since 1.5.0
 *
 */
function wvv_str_contains( $str, $substr ) {
	return strpos( $str, $substr ) !== false;
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
		if ( is_numeric( $response ) ) {
			$uri = sprintf( '/videos/%s', $response );
		} else if ( is_string( $response ) ) { // Support for upload method.
			$id = wvv_uri_to_id( $response );
			if ( is_numeric( $id ) ) {
				$uri = $response;
			}
		}
	}

	return $uri;
}

/**
 * Convert Vimeo URI to ID
 *
 * @param $uri
 *
 * @return mixed
 * @since 1.0.0
 *
 */
function wvv_uri_to_id( $uri ) {

	if ( is_array( $uri ) ) {
		if ( isset( $uri['body']['uri'] ) ) {
			$uri = $uri['body']['uri'];
		}
	}

	if ( ! is_string( $uri ) ) {
		return $uri;
	}

	$parts = explode( '/', $uri );

	return end( $parts );
}

/**
 * Ensure that uri is always uri.
 *
 * @param $id
 *
 * @return string
 */
function wvv_id_to_uri( $id ) {
	if ( is_numeric( $id ) || ! wvv_str_contains( $id, '/' ) ) {
		return '/videos/' . $id;
	} else {
		return $id;
	}
}

/**
 * Format bytes
 *
 * @param $bytes
 * @param int $precision
 *
 * @return string
 */
function wvv_format_bytes( $bytes, $precision = 2 ) {

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

/**
 * Return the vimeo icon url
 *
 * @param null $size
 *
 * @return string
 *
 * @since 1.7.0
 */
function wvv_get_vimeo_icon_url( $size = null ) {
	return sprintf( '%s/%s', WP_VIMEO_VIDEOS_URL, 'admin/img/icon-64.png' );
}

/**
 * Returns the user edit url
 * @param int $id
 *
 * @return string
 *
 * @since 1.7.3
 */
function wvv_get_user_edit_url($id) {
	if(is_object($id) && isset($id->ID)) {
		$id = $id->ID;
	} else if(is_array($id) && isset($id['ID'])) {
		$id = $id['ID'];
	}
	return admin_url(sprintf('user-edit.php?user_id=%s', $id));
}

/**
 * Returns link to the user profile
 * @param $id
 *
 * @return string|void
 *
 * @since 1.7.3
 */
function wvv_get_user_edit_link($id) {

	$user = wp_cache_get('user_'.$id, 'dgv');
	if(false === $user) {
		$user = get_user_by( 'id', $id  );
		wp_cache_set('user_'.$id, $user, 'dgv');
	}

	$name = '';
	$link = '';
	if ( is_a( $user, '\WP_User' ) ) {
		$link = wvv_get_user_edit_url( $user->ID );
		if ( ! empty( $user->display_name ) ) {
			$name = $user->display_name;
		} else if ( ! empty( $user->user_nicename ) ) {
			$name = $user->user_nicename;
		} else if ( ! empty( $user->user_login ) ) {
			$name = $user->user_login;
		}
	}
	return $name ? sprintf( '<a href="%s">%s</a>', $link, $name ) : __( 'Unknown' );
}


