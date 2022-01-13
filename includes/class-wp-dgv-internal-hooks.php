<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of  Video Uploads for Vimeo
 *
 * Video Uploads for Vimeo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 *  Video Uploads for Vimeo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with  Video Uploads for Vimeo. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Internal_Hooks
 *
 * Responsible for all the internal hooks that this plugin defines
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.7.0
 */
class WP_DGV_Internal_Hooks {

	/**
	 * The api helper
	 * @var WP_DGV_Api_Helper
	 */
	protected $api = null;

	/**
	 * The settings helper
	 * @var WP_DGV_Settings_Helper
	 */
	protected $settings = null;

	/**
	 * The logger.
	 * @var null
	 */
	protected $logger = null;

	/**
	 * The db.
	 * @var null
	 */
	protected $db = null;

	/**
	 * WP_DGV_Internal_Hooks constructor.
	 */
	public function __construct() {
		$this->init_utilities();
	}

	/**
	 * Handle after upload hook in Admin area
	 *
	 * @param $args
	 *
	 * @since 1.7.0
	 */
	public function backend_after_upload( $args ) {

		$context = 'backend';
		$logtag  = $this->get_logtag( $context );

		/**
		 * Make sure we are on the right track.
		 */
		if ( ! isset( $args['vimeo_id'] ) ) {
			$this->logger->log( 'No vimeo id found.', $logtag );

			return;
		}

		/**
		 * Obtain some important data.
		 */
		$response = $args['vimeo_id'];
		$uri      = wvv_response_to_uri( $response );

		/**
		 * Signal start
		 */
		$this->logger->log( sprintf( 'Processing hooks for %s', $uri ), $logtag );

		/**
		 * Create local video
		 */
		$this->create_local_video( $args, $context );

		/**
		 * Old deprecated hook
		 */
		do_action( 'dgv_after_upload', $uri, $this->api );
	}


	/**
	 * Init API Helper.
	 * @since 1.7.0
	 */
	protected function init_utilities() {
		if ( is_null( $this->api ) ) {
			$this->api = new WP_DGV_Api_Helper();
		}
		if ( is_null( $this->settings ) ) {
			$this->settings = new WP_DGV_Settings_Helper();
		}
		if ( is_null( $this->logger ) ) {
			$this->logger = new WP_DGV_Logger();
		}
		if ( is_null( $this->db ) ) {
			$this->db = new WP_DGV_Db_Helper();
		}
	}

	/**
	 * Returns the log tag.
	 *
	 * @param $context
	 *
	 * @return string
	 * @since 1.7.0
	 */
	protected function get_logtag( $context ) {
		if ( $context === 'backend' ) {
			$tag = 'DGV-ADMIN-HOOKS';
		} else if ( $context === 'frontend' ) {
			$tag = 'DGV-FRONTEND-HOOKS';
		} else {
			$tag = 'DGV-INTERNAL-HOOKS';
		}

		return $tag;
	}

	/**
	 * Create local video
	 *
	 * @param $data
	 * @param $context
	 *
	 * @since 1.7.0
	 */
	protected function create_local_video( $data, $context ) {
		$logtag      = $this->get_logtag( $context );
		$id          = isset( $data['vimeo_id'] ) ? $data['vimeo_id'] : 0;
		$title       = isset( $data['vimeo_title'] ) ? $data['vimeo_title'] : '';
		$description = isset( $data['vimeo_description'] ) ? $data['vimeo_description'] : '';
		$post_id     = $this->db->create_local_video( $title, $description, $id, 'frontend' );
		$source      = isset( $data['source'] ) ? $data['source'] : array();

		if ( ! is_wp_error( $post_id ) ) {
			/**
			 * Update meta
			 */
			update_post_meta( $post_id, 'dgv_source', $source );
			if ( isset( $data['vimeo_size'] ) && $data['vimeo_size'] ) {
				update_post_meta( $post_id, 'dgv_size', (int) $data['vimeo_size'] );
			}

			/**
			 * Set link to the Video. Note: For some videos Vimeo creates non-standard links.
			 * e.g View privacy: Those with link only.
			 */
			if ( ! empty( $id ) ) {
				try {
					$response = $this->api->get_video_by_id( $id, array( 'link' ) );
					if ( ! empty( $response['body']['link'] ) ) {
						update_post_meta( $post_id, 'dgv_link', $response['body']['link'] );
					}
				} catch ( \Exception $e ) {
				}
			}
			/**
			 * Set media library attachment source
			 */
			if ( isset( $data['source']['media_id'] ) ) {
				update_post_meta( $data['source']['media_id'], 'dgv', array(
					'vimeo_id' => $id,
					'local_id' => $post_id,
				) );
			}
			$this->logger->log( sprintf( 'Local video #%s created', $post_id ), $logtag );
		} else {
			$this->logger->log( sprintf( 'Failed to create local video (%s)', $post_id->get_error_message() ), $logtag );
		}
	}
}
