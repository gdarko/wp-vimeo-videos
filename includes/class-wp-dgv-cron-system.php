<?php

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
		error_log('cleanup executing...');
		wvv_cleanup_local_files();
	}
}