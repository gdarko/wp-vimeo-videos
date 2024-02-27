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

namespace Vimeify\Core\Components;

use Vimeify\Core\Abstracts\Interfaces\CacheInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemComponentInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemInterface;

class Cache implements CacheInterface, SystemComponentInterface {

	/**
	 * The system instance
	 * @var SystemComponentInterface
	 */
	protected $system;

	/**
	 * The constructor
	 * @param SystemInterface $system
	 * @param $args
	 */
	public function __construct( SystemInterface $system, $args = [] ) {
		$this->system = $system;
	}

	/**
	 * Cache remember
	 * @param $key
	 * @param $callback
	 * @param $time
	 *
	 * @return mixed
	 */
	public function remember($key, $callback, $time) {
		$key  = sprintf('vimeify_%s', $key);
		$data = get_transient($key);
		if(false === $data) {
			$data = call_user_func($callback);
			set_transient($key, $data, $time);
		}
		return $data;
	}
}