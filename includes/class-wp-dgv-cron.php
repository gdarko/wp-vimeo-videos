<?php
/********************************************************************
 * Copyright (C) 2022 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of Video Uploads for Vimeo PRO
 *
 * Video Uploads for Vimeo PRO is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Video Uploads for Vimeo PRO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Video Uploads for Vimeo PRO. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Cron
 *
 * Responsible for setting up the Cron events
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.9.0
 */
class WP_DGV_Cron {

	/**
	 * Initialize the corn system
	 *
	 * @param  WP_DGV  $root
	 *
	 * @return void
	 */
	public function init( $root ) {
		$root->get_loader()->add_filter( 'cron_schedules', $this, 'custom_schedules', 9,  1);
		$root->get_loader()->add_action( 'init', $this, 'schedule_actions', 9 );
	}

	/**
	 * Return possible list of cron tasks
	 * @return float[]|int[]
	 */
	private function get_actions() {
		return array(
			'metadata_sync'      => 'dgv_fifteen_minutes',
			'status_sync'        => 'dgv_twenty_minutes',
		);
	}

	/**
	 * Custom cron schedules
	 * @return void
	 */
	public function custom_schedules( $schedules ) {

		$schedules['dgv_fifteen_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => esc_html__( 'Every Fifteen Minutes' ),
		);

		$schedules['dgv_twenty_minutes'] = array(
			'interval' => 20 * MINUTE_IN_SECONDS,
			'display'  => esc_html__( 'Every Twenty Minutes' ),
		);

		return $schedules;
	}

	/**
	 * Registers the cron events
	 */
	public function schedule_actions() {

		foreach ( $this->get_actions() as $key => $recurrence ) {
			$hook      = sprintf( 'wvv_action_%s', $key );
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp == false ) {
				wp_schedule_event( time(), $recurrence, $hook );
			}
			if ( method_exists( $this, 'do_' . $key ) ) {
				add_action( $hook, array( $this, 'do_' . $key ) );
			}
		}
	}

	/**
	 * Sync all the videos in the library with Vimeo.com
	 * @return void
	 */
	public function do_metadata_sync() {
		$logtag = 'DGV-METADATA-SYNC';
		$logger = new WP_DGV_Logger();
		$logger->log( 'Starting metadata sync via cron.', $logtag );
		$sync = new WP_DGV_Sync();
		$sync->sync_metadata();
		$logger->log( 'Finished metadata sync via cron.', $logtag );
	}

	/**
	 * Sync all the videos in the library with Vimeo.com
	 * @return void
	 */
	public function do_status_sync() {
		$logtag = 'DGV-STATUS-SYNC';
		$logger = new WP_DGV_Logger();
		$logger->log( 'Starting status sync via cron.', $logtag );
		$sync = new WP_DGV_Sync();
		$sync->sync_status();
		$logger->log( 'Finished status sync via cron.', $logtag );
	}
}
