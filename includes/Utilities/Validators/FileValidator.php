<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://ideologix.com)
 *
 * This file is part of "Vimeify - Video Uploads for Vimeo"
 *
 * Vimeify - Video Uploads for Vimeo is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Video Uploads for Vimeo is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with "Vimeify - Video Uploads for Vimeo". If not, see <https://www.gnu.org/licenses/>.
 *
 * ---
 *
 * Author Note: This code was written by Darko Gjorgjijoski <dg@darkog.com>
 * If you have any questions find the contact details in the root plugin file.
 *
 **********************************************************************/

namespace Vimeify\Core\Utilities\Validators;

class FileValidator {

	/**
	 * Validate file against what WordPress is set to allow.
	 *
	 * @param  string  $path  Path to a newly uploaded file.
	 * @param  string  $name  Name of a newly uploaded file.
	 *
	 * @return false|string False if no errors found, error text otherwise.
	 *
	 * @since 1.6.0
	 *
	 */
	public function validate_wp_filetype_and_ext( $path, $name ) {

		$wp_filetype = wp_check_filetype_and_ext( $path, $name );

		$ext             = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
		$type            = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
		$proper_filename = empty( $wp_filetype['proper_filename'] ) ? '' : $wp_filetype['proper_filename'];

		if ( $proper_filename || ! $ext || ! $type ) {
			return esc_html__( 'File type is not allowed.', 'wp-vimeo-videos-pro' );
		}

		return false;
	}

	/**
	 * Validate file extension
	 *
	 * @param $ext
	 *
	 * @return false|string
	 *
	 * @since 1.6.0
	 */
	public function validate_extension( $ext ) {

		// Make sure file has an extension first.
		if ( empty( $ext ) ) {
			return esc_html__( 'File must have an extension.', 'wp-vimeo-videos-pro' );
		}

		// Validate extension against all allowed values.
		if ( ! in_array( $ext, $this->allowed_extensions(), true ) ) {
			return esc_html__( 'File type is not allowed.', 'wp-vimeo-videos-pro' );
		}

		return false;
	}

	/*
	*
	 * Return the allowed extensions
	 * @return array
	 */
	public function allowed_extensions() {
		$allowed_extensions = array();
		foreach ( $this->allowed_mimes() as $ext => $mime ) {
			$exts               = explode( '|', $ext );
			$allowed_extensions = array_merge( $allowed_extensions, $exts );
		}

		return apply_filters( 'dgv_allowed_extensions', $allowed_extensions );
	}

	/**
	 * Return the allowed mimetypes
	 * @return string[]
	 */
	public function allowed_mimes() {
		return apply_filters( 'dgv_allowed_mimes', array(
			'mp4|m4v'  => 'video/mp4',
			'mov|qt'   => 'video/quicktime',
			'wmv'      => 'video/x-ms-wmv',
			'avi'      => 'video/avi',
			'flv'      => 'video/x-flv',
			'mts|m2ts' => 'video/MP2T',
		) );
	}




}