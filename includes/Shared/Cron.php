<?php

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Utilities\VimeoSync;

class Cron extends BaseProvider {

	/**
	 * The synchronization processor
	 * @var VimeoSync
	 */
	protected $sync;

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {

		$this->sync = new VimeoSync( $this->plugin );

		add_filter( 'cron_schedules', [ $this, 'custom_schedules' ], 9, 1 );
		add_action( 'init', [ $this, 'schedule_actions' ], 9 );
	}

	/**
	 * Return possible list of cron tasks
	 * @return string[][]
	 */
	private function get_actions() {
		return apply_filters( 'dgv_cron_actions', array(
			'hourly'              => array(
				'cleanup_pull_files'
			),
			'dgv_fifteen_minutes' => array(
				'metadata_sync',
			),
			'dgv_twenty_minutes'  => array(
				'status_sync'
			)
		), $this->plugin );
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

		return apply_filters( 'dgv_cron_schedules', $schedules, $this->plugin );
	}

	/**
	 * Registers the cron events
	 */
	public function schedule_actions() {

		foreach ( $this->get_actions() as $recurrence => $list_of_actions ) {
			foreach ( $list_of_actions as $key ) {
				$hook      = sprintf( 'wvv_action_%s', $key );
				$timestamp = wp_next_scheduled( $hook );
				if ( ! $timestamp ) {
					wp_schedule_event( time(), $recurrence, $hook );
				}
				if ( method_exists( $this, 'do_' . $key ) ) {
					add_action( $hook, array( $this, 'do_' . $key ) );
				} elseif ( is_array( $recurrence ) ) {
					add_action( $hook, $recurrence );
				}
			}
		}
	}

	/**
	 * Clean up pull files
	 * @return void
	 */
	public function do_cleanup_pull_files() {

		$logtag = 'TMP FILE CLEAN UP';

		$this->plugin->system()->logger()->log( 'Starting the temporary files clean up process.', $logtag );

		/**
		 * How many minutes needs to pass in order the temporary uploads used for Vimeo Pull uploading method to be removed.
		 * Warning: please make sure you leave at least 30 minutes for Vimeo to process the file. Setting this value to low may cause missing uploads.
		 */
		$removal_delay_minutes = apply_filters( 'dgv_upload_pull_removal_delay', 180 );
		if ( $removal_delay_minutes < 20 ) { // Protection for just in case.
			$removal_delay_minutes = 100;
		}

		$time_now  = time();
		$tmp_files = $this->plugin->system()->settings()->get_temporary_files();
		if ( count( $tmp_files ) > 0 ) {
			foreach ( $tmp_files as $path => $time_age ) {
				$diff_minutes = round( abs( $time_now - $time_age ) / 60, 2 );
				$file_exists  = file_exists( $path );
				if ( $diff_minutes >= $removal_delay_minutes && $file_exists ) {
					if ( unlink( $path ) ) {
						$this->plugin->system()->settings()->remove_from_temporary_files( $path );
						$this->plugin->system()->logger()->log( sprintf( 'Deleted temporary video file %s after %s minutes', $path, $diff_minutes ), $logtag );
					} else {
						$this->plugin->system()->logger()->log( sprintf( 'Unable to remove temporary video file %s.', $path ), $logtag );
					}
				} elseif ( ! $file_exists ) {
					$this->plugin->system()->settings()->remove_from_temporary_files( $path );
					$this->plugin->system()->logger()->log( sprintf( 'Temporary video file %s not found in the file system', $path ), $logtag );
				}
			}
		} else {
			$this->plugin->system()->logger()->log( 'No temporary files found for clean up.', $logtag );
		}
	}

	/**
	 * Sync all the videos in the library with Vimeo.com
	 * @return void
	 */
	public function do_metadata_sync() {
		$logtag = 'DGV-METADATA-SYNC';
		$this->plugin->system()->logger()->log( 'Starting metadata sync via cron.', $logtag );
		$this->sync->sync_metadata();
		$this->plugin->system()->logger()->log( 'Finished metadata sync via cron.', $logtag );
	}

	/**
	 * Sync all the videos in the library with Vimeo.com
	 * @return void
	 */
	public function do_status_sync() {
		$logtag = 'DGV-STATUS-SYNC';
		$this->plugin->system()->logger()->log( 'Starting status sync via cron.', $logtag );
		$this->sync->sync_status();
		$this->plugin->system()->logger()->log( 'Finished status sync via cron.', $logtag );
	}
}