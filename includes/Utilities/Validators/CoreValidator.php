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

class CoreValidator {

	/**
	 * Is the vimeo minimum php version satisfied?
	 * @return bool
	 * @since    1.0.0
	 */
	public function is_version_met( $version ) {
		return version_compare( PHP_VERSION, $version, '>=' );
	}

	/**
	 * Check if function is available
	 *
	 * @param $name
	 *
	 * @return bool
	 *
	 * @since 1.6.0
	 */
	public function is_function_available( $name ) {
		static $available;
		if ( ! isset( $available ) ) {
			if ( ! function_exists( $name ) ) {
				$available = false;
			} else {
				$available = true;
				$d         = ini_get( 'disable_functions' );
				$s         = ini_get( 'suhosin.executor.func.blacklist' );
				if ( "$d$s" ) {
					$array = preg_split( '/,\s*/', "$d,$s" );
					if ( in_array( $name, $array ) ) {
						$available = false;
					}
				}
			}
		}

		return $available;
	}

}