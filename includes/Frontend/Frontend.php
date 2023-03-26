<?php

namespace Vimeify\Core\Frontend;

use Vimeify\Core\Abstracts\BaseProvider;

class Frontend extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		$this->boot( Scripts::class );
		$this->boot( Hooks::class );
	}
}