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