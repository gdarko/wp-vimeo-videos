<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of Video Uploads for Vimeo
 *
 * Video Uploads for Vimeo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Video Uploads for Vimeo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Video Uploads for Vimeo. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Cron_System
 *
 * Responsible for setting up the Cron events
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV_Cron_System {

	/**
	 * Registers the cron events
	 */
	public function register_events() {
		if ( ! wp_next_scheduled( 'wvv_event_clean_local_files' ) ) {
			wp_schedule_event( time(), 'wvv_every_five_minutes', 'wvv_event_clean_local_files' );
		}
	}

	/**
	 * Registers the required cron schedules
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function cron_schedules( $schedules ) {
		if ( ! isset( $schedules["wvv_every_five_minutes"] ) ) {
			$schedules["wvv_every_five_minutes"] = array(
				'interval' => 5 * 60,
				'display'  => __( 'Once every 5 minutes' )
			);
		}

		return $schedules;
	}

	/**
	 * Clean up local files
	 */
	public function cleanup() {
	    // Do nothing for now
	}
}
