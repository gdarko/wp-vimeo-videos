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

namespace Vimeify\Core\Components;

use Vimeify\Core\Abstracts\Interfaces\DatabaseInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemComponentInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemInterface;
use Vimeify\Core\Backend\Ui;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;

/**
 * Class Database
 *
 * Responsible for communicating with the database
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @package Vimeify\Core
 * @since 1.9.2
 */
class Database implements DatabaseInterface, SystemComponentInterface {

	const POST_TYPE_UPLOADS = 'dgv-upload';
	const TAX_CATEGORY = 'dgv-category';
	const POST_TYPE_UPLOAD_PROFILES = 'dgv-uprofile';

	/**
	 * The WPDB Instance
	 * @var \wpdb
	 */
	protected $db;

	/**
	 * The system interface
	 * @var SystemInterface
	 */
	protected $system;

	/**
	 * Constructor
	 *
	 * @param SystemInterface $system
	 * @param $args
	 */
	public function __construct( SystemInterface $system, $args = [] ) {
		$this->system = $system;
		global $wpdb;
		$this->db = &$wpdb;
	}

	/**
	 * Return all the vimeo videos from the local database.
	 *
	 * @param array $args
	 * @param string $type
	 *
	 * @return int[]|WP_Post[]|WP_Query
	 */
	public function get_videos( $args = array(), $type = 'items' ) {
		$params = array(
			'post_type'      => self::POST_TYPE_UPLOADS,
			'posts_per_page' => isset( $args['number'] ) ? $args['number'] : - 1,
			'offset'         => isset( $args['offset'] ) ? $args['offset'] : 0,
			'post_status'    => 'publish'
		);

		if ( isset( $args['paged'] ) ) {
			$params['paged'] = (int) $args['paged'];
		}
		if ( isset( $args['author'] ) ) {
			$params['author'] = $args['author'];
		}
		if ( isset( $args['s'] ) ) {
			$params['s'] = $args['s'];
		}

		if ( 'items' === $type ) {
			$result = get_posts( $params );
		} else {
			$result = new \WP_Query( $params );
		}

		return $result;
	}

	/**
	 * Return the vimeo uri for specific local vimeo video.
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function get_vimeo_uri( $post_id ) {
		$response = $this->get_vimeo_id( $post_id );

		$vimeo_formatter = new VimeoFormatter();

		return $response ? $vimeo_formatter->id_to_uri( $response ) : $response;
	}

	/**
	 * Return the vimeo video id for specific local vimeo video.
	 *
	 * @param $post_id
	 * @param int
	 *
	 * @return mixed
	 */
	public function get_vimeo_id( $post_id ) {
		$vimeo_id = get_post_meta( $post_id, 'dgv_response', true );
		if ( $vimeo_id ) {
			$vimeo_id = str_replace( '/videos/', '', $vimeo_id );
		}

		return $vimeo_id;
	}

	/**
	 * Returns local vimeo video post id.
	 *
	 * @param $vimeo_uri_or_id
	 *
	 * @return string|null
	 */
	public function get_post_id( $vimeo_uri_or_id ) {

		$formatter = new VimeoFormatter();
		$vimeo_id  = $formatter->uri_to_id( $vimeo_uri_or_id ); // Ensure id.
		$table     = $this->db->postmeta;
		$query     = $this->db->prepare( "SELECT post_id FROM {$table} PM WHERE PM.meta_key='dgv_response' AND PM.meta_value='%s'", $vimeo_id );

		return $this->db->get_var( $query );
	}

	/**
	 * Return vimeo link
	 *
	 * @param $post_id
	 *
	 * @return mixed|string
	 */
	public function get_vimeo_link( $post_id ) {
		$vimeo_link = get_post_meta( $post_id, 'dgv_link', true );
		if ( empty( $vimeo_link ) ) {
			$vimeo_id   = $this->get_vimeo_id( $post_id );
			$vimeo_link = sprintf( 'https://vimeo.com/%s', $vimeo_id );
		}

		return $vimeo_link;
	}

	/**
	 * Returns the local video
	 *
	 * @param $title
	 * @param $description
	 * @param $vimeo_id - (eg. 18281821)
	 * @param string $context
	 *
	 * @return int|WP_Error
	 */
	public function create_local_video( $title, $description, $vimeo_id, $context = 'admin' ) {

		$vimeo_formatter = new VimeoFormatter();

		$vimeo_id = $vimeo_formatter->uri_to_id( $vimeo_id );

		// Do not create local video if it already exists.
		$postID = $this->get_post_id( $vimeo_id );
		if ( ! empty( $postID ) ) {
			return $postID;
		}

		$args = array(
			'post_title'   => wp_strip_all_tags( $title ),
			'post_content' => wp_strip_all_tags( $description ),
			'post_status'  => 'publish',
			'post_type'    => Database::POST_TYPE_UPLOADS,
			'post_author'  => is_user_logged_in() ? get_current_user_id() : 0,
		);

		$args = apply_filters( 'dgv_insert_video_args', $args, $context ); // Deprecated.
		$args = apply_filters( 'dgv_before_create_local_video_params', $args, $vimeo_id, $context );

		$postID = wp_insert_post( $args );

		if ( ! is_wp_error( $postID ) ) {
			update_post_meta( $postID, 'dgv_response', $vimeo_id );
		}

		update_post_meta( $postID, 'dgv_context', $context );

		return $postID;
	}

	/**
	 * Deletes a local video
	 *
	 * @param $post_id
	 *
	 * @return array|false|WP_Post|null
	 */
	public function delete_local_video( $post_id ) {
		return wp_delete_post( $post_id, true );
	}

	/**
	 * Check for uploads
	 *
	 * @param bool $current_user_uploads_only
	 *
	 * @return array
	 */
	public function get_uploaded_videos( $current_user_uploads_only = false ) {
		$uploads_formatted = wp_cache_get( 'wvv_uploads_formatted' );
		if ( false === $uploads_formatted ) {
			$params = array();
			if ( $current_user_uploads_only ) {
				$params['author'] = get_current_user_id();
			}
			$params            = apply_filters( 'dgv_uploaded_videos_query_args', $params );
			$uploads           = $this->get_videos( $params );
			$uploads_formatted = array();
			foreach ( $uploads as $_upload ) {
				$uploads_formatted[] = array(
					'title'    => $_upload->post_title,
					'vimeo_id' => $this->get_vimeo_id( $_upload->ID ),
					'ID'       => $_upload->ID
				);
			}
			wp_cache_set( 'wvv_uploads_formatted', $uploads_formatted );
		}

		return $uploads_formatted;
	}

	/**
	 * Set the video metadata.
	 *
	 * @param $post_id
	 * @param $metadata
	 *
	 * @return void
	 *
	 * @since 1.9.2
	 */
	public function set_metadata( $post_id, $metadata ) {
		foreach ( $metadata as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
		update_post_meta( $post_id, 'dgv_last_sync_at', time() );
	}

	/**
	 * Returns the total uploaded size
	 * @return int
	 */
	public function get_total_uploaded_size( $args = [] ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT SUM(PM.meta_value) FROM $wpdb->posts P, $wpdb->postmeta PM WHERE P.ID=PM.post_id AND meta_key='dgv_size'" );

		if ( ! empty( $args['author'] ) ) {
			$query .= $wpdb->prepare( ' AND P.post_author=%d', (int) $args['author'] );
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * The edit link
	 * @return string
	 */
	public function get_edit_link( $post_id ) {
		$legacy = apply_filters( 'dgv_legacy_edit_link', false );

		return $legacy ? admin_url( 'admin.php?page=' . Ui::PAGE_VIMEO . '&action=edit&id=' . esc_attr( $post_id ) ) : get_edit_post_link( $post_id );
	}
}
