<?php

namespace Vimeify\Core\Utilities;

class FileSystem {

	/**
	 * Check if file exists
	 * @return bool
	 */
	public static function file_exists( $path ) {
		return \file_exists( $path );
	}

	/**
	 *  Set correct file permissions for specific file.
	 *
	 * @param $path
	 *
	 * @since 1.6.0
	 */
	public static function set_file_permissions( $path ) {
		$stat = stat( dirname( $path ) );
		@chmod( $path, $stat['mode'] & 0000666 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Add trailing slash to path
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public static function slash( $path ) {
		if ( function_exists( '\trailingslashit' ) ) {
			return \trailingslashit( $path );
		}

		return self::unslash( $path ) . '/';
	}

	/**
	 * Unslash path
	 * @param $path
	 *
	 * @return string
	 */
	public static function unslash( $path ) {

		if ( function_exists( '\untrailingslashit' ) ) {
			return \untrailingslashit( $path );
		}

		return rtrim( $path, '/\\' );
	}
}