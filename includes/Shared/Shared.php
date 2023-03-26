<?php

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseProvider;

class Shared extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		$this->boot( Cron::class );
		$this->boot( Hooks::class );
		$this->boot( Migrations::class );
		$this->boot( PostTypes::class );
		$this->boot( Scripts::class );
	}
}