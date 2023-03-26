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

}