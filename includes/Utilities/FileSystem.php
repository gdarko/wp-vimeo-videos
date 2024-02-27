<?php
/********************************************************************
 * Copyright (C) 2024 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2024 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/vimeify/
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