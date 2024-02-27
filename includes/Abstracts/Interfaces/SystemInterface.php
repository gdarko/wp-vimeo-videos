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

namespace Vimeify\Core\Abstracts\Interfaces;

use Vimeify\Core\Utilities\TemporaryDirectory;

interface SystemInterface {

	/**
	 * The Vimeo API
	 * @return VimeoInterface
	 */
	public function vimeo();

	/**
	 * The Database API
	 * @return DatabaseInterface
	 */
	public function database();

	/**
	 * The Settings API
	 * @return SettingsInterface
	 */
	public function settings();

	/**
	 * The Settings API
	 * @return LoggerInterface
	 */
	public function logger();

	/**
	 * The Views API
	 * @return ViewsInterface
	 */
	public function views();

	/**
	 * The Requests API
	 * @return RequestsInterface
	 */
	public function requests();

	/**
	 * The Cache API
	 * @return CacheInterface
	 */
	public function cache();

	/**
	 * The config data
	 * @return array
	 */
	public function config( $key = null, $default = null );

	/**
	 * Returns the tmp dir path
	 * @return TemporaryDirectory
	 */
	public function tmp_dir();

}