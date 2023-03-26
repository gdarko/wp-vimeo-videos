<?php

namespace Vimeify\Core\Utilities\Validators;

class NetworkValidator {

	/**
	 * Validate domain name
	 *
	 * @param $domain_name
	 *
	 * @return bool
	 */
	public function validate_domain_name( $domain_name ) {
		return ( preg_match( "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name ) //valid chars check
		         && preg_match( "/^.{1,253}$/", $domain_name ) //overall length check
		         && preg_match( "/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name ) ); //length of each label
	}

}