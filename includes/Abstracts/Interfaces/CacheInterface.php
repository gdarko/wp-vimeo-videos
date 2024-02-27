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

interface CacheInterface {

	const MINUTE_IN_SECONDS = 60;
	const HOUR_IN_SECONDS = 60 * 60;
	const DAY_IN_SECONDS = 1 * ( 24 * ( 60 * 60 ) );
	const WEEK_IN_SECONDS = 7 * ( 24 * ( 60 * 60 ) );
	const MONTH_IN_SECONDS = 30 * ( 24 * ( 60 * 60 ) );
	const YEAR_IN_SECONDS = 365 * ( 24 * ( 60 * 60 ) );

	/**
	 * Cache remember
	 *
	 * @param $key
	 * @param $callback
	 * @param $time
	 *
	 * @return mixed
	 */
	public function remember( $key, $callback, $time );

}