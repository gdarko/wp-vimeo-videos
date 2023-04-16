<?php

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