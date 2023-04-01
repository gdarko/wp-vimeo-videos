<?php

namespace Vimeify\Core\Abstracts\Interfaces;

interface IntegrationInterface {

	/**
	 * Check if the integration can be activated.
	 * @return bool
	 */
	public function can_activate();

	/**
	 * Activates the integration
	 * @return bool
	 */
	public function activate();
}