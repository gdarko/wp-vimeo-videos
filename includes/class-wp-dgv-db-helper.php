<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of WP Vimeo Videos
 *
 * WP Vimeo Videos is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * WP Vimeo Videos is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Vimeo Videos. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Db_Helper
 *
 * Responsible for communicating with the database
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV_Db_Helper {

	const POST_TYPE_UPLOADS = 'dgv-upload';

	/**
	 * The WPDB Instance
	 * @var wpdb
	 */
	protected $db;

	/**
	 * WP_DGV_Db_Helper constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db = &$wpdb;
	}

	/**
	 * Return all the vimeo videos from the local database.
	 *
	 * @param array $args
	 *
	 * @return int[]|WP_Post[]
	 */
	public function get_videos( $args = array() ) {
		$params = array(
			'post_type'      => self::POST_TYPE_UPLOADS,
			'posts_per_page' => isset( $args['number'] ) ? $args['number'] : - 1,
			'offset'         => isset( $args['offset'] ) ? $args['offset'] : 0,
			'post_status'    => 'publish'
		);

		if ( isset( $args['author'] ) ) {
			$params['author'] = $args['author'];
		}

		$posts  = get_posts( $params );

		return $posts;
	}

	/**
	 * Return total videos count
	 * @return string|null
	 */
	public function get_videos_count() {
		$query = $this->db->prepare( "SELECT COUNT(*) FROM {$this->db->posts} P WHERE P.post_status='publish' AND P.post_type=%s", self::POST_TYPE_UPLOADS );
		$count = $this->db->get_var( $query );

		return $count;
	}

	/**
	 * Return the vimeo uri for specific local vimeo video.
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function get_vimeo_uri( $post_id ) {
		$response = get_post_meta( $post_id, 'dgv_response', true );

		return $response;
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
		$vimeo_uri = $this->get_vimeo_uri( $post_id );
		$vimeo_id  = str_replace( '/videos/', '', $vimeo_uri );

		return $vimeo_id;
	}

	/**
	 * Returns local vimeo video post id.
	 *
	 * @param $vimeo_uri
	 *
	 * @return string|null
	 */
	public function get_post_id( $vimeo_uri ) {
		$table     = $this->db->postmeta;
		$query     = $this->db->prepare( "SELECT post_id FROM {$table} PM WHERE PM.meta_key='dgv_response' AND PM.meta_value='%s'", $vimeo_uri );
		return $this->db->get_var( $query );
	}

	/**
	 * Set the database defaults
	 */
	public function set_defaults() {}

	/**
	 * Returns the local video
	 * @param $title
	 * @param $description
	 * @param $uri
	 *
	 * @return int|WP_Error
	 */
	public function create_local_video($title, $description, $uri) {
		$postID = wp_insert_post( array(
			'post_title'   => wp_strip_all_tags( $title ),
			'post_content' => wp_strip_all_tags( $description ),
			'post_status'  => 'publish',
			'post_type'    => WP_DGV_Db_Helper::POST_TYPE_UPLOADS,
			'post_author'  => get_current_user_id(),
		) );

		if(!is_wp_error($postID)) {
			update_post_meta($postID, 'dgv_response', $uri);
		}
		return $postID;
	}

	/**
	 * Check for uploads
	 * @return array
	 */
	public function get_uploaded_videos() {
        $uploads_formatted = wp_cache_get('wvv_uploads_formatted');
        if (false === $uploads_formatted) {
            $params            = apply_filters('dgv_uploaded_videos_query_args', array());
            $uploads           = $this->get_videos($params);
            $uploads_formatted = array();
            foreach ($uploads as $_upload) {
                $uploads_formatted[] = array(
                    'title'    => $_upload->post_title,
                    'vimeo_id' => $this->get_vimeo_id($_upload->ID),
                    'ID'       => $_upload->ID
                );
            }
            wp_cache_set('wvv_uploads_formatted', $uploads_formatted);
        }

        return $uploads_formatted;
	}
}