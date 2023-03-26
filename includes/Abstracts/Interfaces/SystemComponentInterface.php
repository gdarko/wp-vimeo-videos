<?php

namespace Vimeify\Core\Abstracts\Interfaces;

interface SystemComponentInterface {
	public function __construct( SystemInterface $system, $args = [] );
}