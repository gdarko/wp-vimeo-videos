<?php

namespace Vimeify\Core\Utilities\Validators;

class RequestValidator {

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
					esc_html__( 'File exceeds max size allowed (%s).', 'wp-vimeo-videos-pro' ),
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
}