<?php
/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
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

namespace Vimeify\Core\Utilities;

class VimeoSync {

	const SAFE_REMAINING_API_REQUESTS_TRESHOLD = 3;

	/**
	 * The sync process
	 * @var \Vimeify\Core\Plugin
	 */
	protected $plugin;

	/**
	 * Constructor
	 *
	 * @param  \Vimeify\Core\Abstracts\Interfaces\PluginInterface  $plugin
	 */
	public function __construct( &$plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Sync the videos that were deleted.
	 * @return int
	 */
	public function sync_status( $fresh = false ) {

		$tag     = 'DGV-STATUS-SYNC';
		$deleted = 0;

		if ( $fresh ) {
			$this->delete_lock( 'status_sync' );
		}

		if ( $this->check_lock( 'status_sync' ) ) {
			$this->plugin->system()->logger()->log( 'Video status sync - Already in progress.', $tag, 'cron.log' );

			return $deleted;
		}

		if ( ! $this->plugin->system()->vimeo()->is_connected ) {
			$this->plugin->system()->logger()->log( 'Video status sync - API not connected, skipping sync.', $tag, 'cron.log' );

			return $deleted;
		}

		set_time_limit( 0 );
		$this->set_lock( 'status_sync' );

		$sync = $this->get_last_status_sync();
		if ( empty( $sync ) ) {
			$sync = [ 'videos' => [], 'last_pos' => 0, 'last_synced_at' => time() ];
			$this->plugin->system()->logger()->log( 'Video status sync - Syncing for the first time. Preparing...' );
		}

		if ( empty( $sync['videos'] ) ) {
			global $wpdb;
			$sync['videos'] = $wpdb->get_results( "SELECT PM.post_id, PM.meta_value as vimeo_id from $wpdb->postmeta PM WHERE PM.meta_key='dgv_response' ORDER BY PM.meta_id ASC", ARRAY_A );
			$this->plugin->system()->logger()->log( 'Video status sync - No unsynced local videos found from previous iteration, restarting from scratch', $tag, 'cron.log' );
		}

		if ( ! empty( $sync['videos'] ) ) {

			$video_count = count( $sync['videos'] );
			$this->plugin->system()->logger()->log( sprintf( 'Video status sync - Syncing %d videos, starting from position %d', $video_count, $sync['last_pos'] + 1 ), $tag, 'cron.log' );

			$call_quota = $this->plugin->system()->vimeo()->get_calls_quota();
			$max_calls  = $call_quota['limit']; // initial.
			for ( $i = $sync['last_pos'] + 1; $i < $video_count; $i ++ ) {

				// Save last position
				$sync['last_pos'] = $i;
				$this->plugin->system()->logger()->log( sprintf( 'Video status sync - Syncing local video #%d', $sync['videos'][ $i ]['post_id'] ), $tag, 'cron.log' );

				// Break of the loop if reaching low api quota, try again later where yu left off.
				if ( $max_calls <= self::SAFE_REMAINING_API_REQUESTS_TRESHOLD ) {
					$sync['last_pos'] --; // Re-attempt? - Yes.
					$this->plugin->system()->logger()->log( sprintf( 'Video status sync - Reached low api calls quota (%d), continuing in the next iteration', $max_calls ), $tag, 'cron.log' );
					break;
				}
				try {
					$response = $this->plugin->system()->vimeo()->get_video_by_id( $sync['videos'][ $i ]['vimeo_id'] );

					// Update max requests
					if ( isset( $response['headers']['x-ratelimit-remaining'] ) ) {
						$max_calls = (int) $response['headers']['x-ratelimit-remaining'];
						$this->plugin->system()->logger()->log( sprintf( 'Video status sync - Remaining calls re-acquired (%d)', $max_calls ), $tag, 'cron.log' );
					} else {
						$this->plugin->system()->logger()->log( sprintf( 'Video status sync - Remaining calls reduced (%d)', $max_calls ), $tag, 'cron.log' );
						$max_calls --;
					}

					if ( isset( $response['status'] ) && $response['status'] === 429 ) {
						// Quota exceed!
						$this->plugin->system()->logger()->log( 'Video status sync - API Quota exceeded, continuing in the next iteration', $tag, 'cron.log' );
						$sync['last_pos'] --; // Re-attempt? - Yes.
						break;
					}
					if ( isset( $response['status'] ) && $response['status'] === 404 ) {
						$this->plugin->system()->database()->delete_local_video( $sync['videos'][ $i ]['post_id'] );
						$this->plugin->system()->logger()->log( 'Video status sync - Remote video not found, local video deleted', $tag, 'cron.log' );
						$deleted ++;
					} else {
						$this->plugin->system()->logger()->log( 'Video status sync - Remote video found, preserving local video', $tag, 'cron.log' );
					}

				} catch ( \Exception $e ) {
					$this->delete_lock( 'status_sync' );
				}
				$this->plugin->system()->logger()->log( sprintf( 'Video status sync - Just synced position %d', $sync['last_pos'] ), $tag, 'cron.log' );
			}

			$sync['last_synced_at'] = time();
			if ( ( $video_count - 1 ) === $sync['last_pos'] ) {
				$sync['videos']   = [];
				$sync['last_pos'] = 0;
				$this->plugin->system()->logger()->log( 'Video status sync - Reached end, restarting', $tag, 'cron.log' );
			}
			$this->set_last_status_sync( $sync );
		}

		$this->delete_lock( 'status_sync' );

		return $deleted;

	}

	/**
	 * Sync the videos metadata
	 * @return int
	 */
	public function sync_metadata( $fresh = false ) {

		$tag    = 'DGV-METADATA-SYNC';
		$synced = 0;

		if ( $fresh ) {
			$this->delete_lock( 'metadata_sync' );
		}

		if ( $this->check_lock( 'metadata_sync' ) ) {
			$this->plugin->system()->logger()->log( 'Metadata sync - Already in progress.', $tag, 'cron.log' );

			return $synced;
		}

		if ( ! $this->plugin->system()->vimeo()->is_connected ) {
			$this->plugin->system()->logger()->log( 'Metadata sync - API not connected, skipping sync.', $tag, 'cron.log' );

			return $synced;
		}

		set_time_limit( 0 );

		$this->set_lock( 'metadata_sync' );

		$last_sync = $this->get_last_metadata_sync();

		if ( isset( $last_sync['latest_page'] ) && isset( $last_sync['total_pages'] ) && $last_sync['latest_page'] < $last_sync['total_pages'] ) {
			$next_page = $last_sync['latest_page'] + 1;
			$this->plugin->system()->logger()->log( sprintf( 'Metadata sync - Starting sync from where we last stopped at page %d', $next_page ), $tag, 'cron.log' );
		} else {
			$next_page = 1;
			$this->plugin->system()->logger()->log( sprintf( 'Metadata sync - Starting sync from page %d', 1 ), $tag, 'cron.log' );
		}

		try {

			$result = $this->plugin->system()->vimeo()->get_uploaded_videos_safe( [
				'per_page'  => 100,
				'page'      => $next_page,
				'filter'    => 'playable',
				'sort'      => 'date',
				'direction' => 'asc',
				'fields'    => 'uri,name,description,size,duration,link,player_embed_url,width,height,is_playable,pictures.sizes',
			] );
			if ( ! empty( $result['videos'] ) ) {
				foreach ( $result['videos'] as $video ) {
					$synced ++;
					$post_id = $this->plugin->system()->database()->get_post_id( $video['uri'] );
					if ( empty( $post_id ) ) {
						continue;
					}
					$this->plugin->system()->vimeo()->set_local_video_metadata( $post_id, $video );
					$this->plugin->system()->logger()->log( sprintf( 'Metadata sync - Processed video #%d (%s)', $post_id, $video['uri'] ), $tag, 'cron.log' );
				}
			}

			$last_sync = $result;
			unset( $last_sync['videos'] );
			$this->set_last_metadata_sync( $last_sync );
			$this->plugin->system()->logger()->log( 'Metadata sync - Done for this iteration. Saved state.', $tag, 'cron.log' );


		} catch ( \Exception $e ) {
			$this->plugin->system()->logger()->log( 'etadata sync - Error acqueruing results from API: ' . $e->getMessage(), $tag, 'cron.log' );
			$this->delete_lock( 'metadata_sync' );
		}

		$this->delete_lock( 'metadata_sync' );

		return $synced;

	}

	/**
	 * Return the last sync details
	 * @return array
	 */
	private function get_last_metadata_sync() {
		$data = get_option( 'dgv_last_metadata_sync' );
		if ( ! is_array( $data ) ) {
			$data = [];
		}

		return $data;
	}

	/**
	 * Set the last metadata sync
	 *
	 * @param $data
	 *
	 * @return void
	 */
	private function set_last_metadata_sync( $data ) {
		update_option( 'dgv_last_metadata_sync', $data );
	}

	/**
	 * Return the last status sync
	 * @return array
	 */
	private function get_last_status_sync() {
		$data = get_option( 'dgv_last_status_sync' );
		if ( ! is_array( $data ) ) {
			$data = [];
		}

		return $data;
	}

	/**
	 * Set the last status sync
	 *
	 * @param $data
	 *
	 * @return void
	 */
	private function set_last_status_sync( $data ) {
		if ( is_null( $data ) || empty( $data ) ) {
			delete_option( 'dgv_last_status_sync' );
		} else {
			update_option( 'dgv_last_status_sync', $data );
		}
	}

	/**
	 * Makes a lock
	 *
	 * @param $type
	 * @param $expiry
	 *
	 * @return void
	 */
	public function set_lock( $type, $expiry = MINUTE_IN_SECONDS * 2 ) {
		set_transient( 'dgv_' . $type . '_lock', 1, $expiry );
	}

	/**
	 * Frees a lock
	 *
	 * @param $type
	 *
	 * @return void
	 */
	public function delete_lock( $type ) {
		delete_transient( 'dgv_' . $type . '_lock' );
	}

	/**
	 * Check if process is locked
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function check_lock( $type ) {
		return 1 === (int) get_transient( 'dgv_' . $type . '_lock' );
	}

}