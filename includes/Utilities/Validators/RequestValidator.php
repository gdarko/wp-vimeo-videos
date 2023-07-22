<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
 *
 * Vimeify - Formerly "WP Vimeo Videos" is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this plugin. If not, see <https://www.gnu.org/licenses/>.
 *
 * Code developed by Darko Gjorgjijoski <dg@darkog.com>.
 **********************************************************************/

namespace Vimeify\Core\Utilities\Validators;

class RequestValidator {

	/**
	 * Is localhost?
	 * @param $whitelist
	 *
	 * @return bool
	 */
	public function validate_localhost( $whitelist = [ '127.0.0.1', '::1' ] ) {
		if(!isset($_SERVER['REMOTE_ADDR'])) {
			return true;
		}
		return in_array( $_SERVER['REMOTE_ADDR'], $whitelist );
	}

	/***
	 * Validates request max size.
	 *
	 * @param $max_size
	 * @param  null  $sizes
	 *
	 * @return false|string
	 *
	 * @since 1.6.0
	 *
	 */
	public function validate_upload_max_filesize( $max_size, $sizes = null ) {

		if ( null === $sizes && ! empty( $_FILES ) ) {
			$sizes = [];
			foreach ( $_FILES as $file ) {
				$sizes[] = $file['size'];
			}
		}

		if ( ! is_array( $sizes ) ) {
			return false;
		}

		$max_size = min( wp_max_upload_size(), $max_size );

		foreach ( $sizes as $size ) {
			if ( $size > $max_size ) {
				return sprintf( /* translators: $s - allowed file size in Mb. */
					esc_html__( 'File exceeds max size allowed (%s).', 'wp-vimeo-videos' ),
					size_format( $max_size )
				);
			}
		}

		return false;
	}

	/**
	 * Convert a file size provided, such as "2M", to bytes.
	 *
	 * @link http://stackoverflow.com/a/22500394
	 *
	 * @since 1.6.0
	 *
	 * @param  bool  $bytes
	 *
	 * @return mixed
	 */
	public function max_upload_size( $bytes = false ) {

		$max = wp_max_upload_size();
		if ( $bytes ) {
			return $max;
		}

		return size_format( $max );
	}


	/**
	 * Basic file upload validation.
	 *
	 * @param  int  $error  Error ID provided by PHP.
	 *
	 * @return false|string False if no errors found, error text otherwise.
	 *
	 * @since 1.6.0
	 *
	 */
	public static function get_file_upload_error( $error ) {

		if ( 0 === $error || 4 === $error ) {
			return false;
		}

		$errors = array(
			false,
			esc_html__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'wp-vimeo-videos' ),
			esc_html__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'wp-vimeo-videos' ),
			esc_html__( 'The uploaded file was only partially uploaded.', 'wp-vimeo-videos' ),
			esc_html__( 'No file was uploaded.', 'wp-vimeo-videos' ),
			'',
			esc_html__( 'Missing a temporary folder.', 'wp-vimeo-videos' ),
			esc_html__( 'Failed to write file to disk.', 'wp-vimeo-videos' ),
			esc_html__( 'File upload stopped by extension.', 'wp-vimeo-videos' ),
		);

		if ( array_key_exists( $error, $errors ) ) {
			return sprintf( esc_html__( 'File upload error. %s', 'wp-vimeo-videos' ), $errors[ $error ] );
		}

		return false;
	}

	/**
	 * Set up the error messages
	 *
	 * @param $key
	 *
	 * @return mixed|string|void
	 *
	 * @since 1.6.0
	 */
	public static function get_upload_error( $key ) {
		$messages = array(
			'invalid_vimeo_video' => __( 'Invalid video vimeo provided', 'wp-vimeo-videos' ),
			'invalid_file'        => __( 'Video file is required. Please pick a valid video file.', 'wp-vimeo-videos' ),
			'invalid_title'       => __( 'Title is required. Please specify valid video title.', 'wp-vimeo-videos' ),
			'not_connected'       => __( 'Unable to connect to Vimeo for the file upload.', 'wp-vimeo-videos' ),
			'not_authenticated'   => __( 'Connection to Vimeo is successful. However we detected that the connection is made with unauthenticated access token. To connect to Vimeo successfully "Authenticated" Access Token is required with the proper Scopes selected.', 'wp-vimeo-videos' ),
			'cant_upload'         => __( 'Connection to Vimeo is successful. However we detected that the current Access Token is missing the Upload scope. To be able to upload Videos successfully "Authenticated" Access Token is required with all the Scopes selected.', 'wp-vimeo-videos' ),
			'quota_limit'         => __( 'Sorry, the current remaining quota in the Vimeo account is %s and this file is %s. Therefore the video can not be uploaded because the Vimeo account doesn\'t have enough free space.', 'wp-vimeo-videos' ),
		);
		if ( ! isset( $messages[ $key ] ) ) {
			return __( 'Something went wrong. Please try again later.', 'wp-vimeo-videos' );
		} else {
			return $messages[ $key ];
		}
	}
}