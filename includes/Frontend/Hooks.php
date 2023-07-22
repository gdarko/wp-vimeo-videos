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

namespace Vimeify\Core\Frontend;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Components\Database;

class Hooks extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_shortcode( 'vimeo_video', array( $this, 'shortcode_video' ) ); // DEPRECATED.
		add_shortcode( 'dgv_vimeo_video', array( $this, 'shortcode_video' ) ); // DEPRECATED.
		add_shortcode( 'vimeify_video', array( $this, 'shortcode_video' ) );
		add_filter( 'the_content', [ $this, 'video_contents' ] );
	}

	/**
	 * The video shortcode
	 *
	 * @param $atts
	 *
	 * @return false|string
	 */
	public function shortcode_video( $atts ) {
		$a        = shortcode_atts( array( 'id' => '', ), $atts );
		$content  = '';
		$video_id = isset( $a['id'] ) ? $a['id'] : null;

		$pre_output = apply_filters( 'dgv_shortcode_pre_output', null, $video_id, $this->plugin );
		if ( ! is_null( $pre_output ) ) {
			return $pre_output;
		}

		if ( ! empty( $video_id ) ) {
			wp_enqueue_style( $this->plugin->slug() );
			$content = $this->plugin->system()->views()->get_view( 'frontend/partials/video', array(
				'vimeo_id' => $video_id
			) );
		}

		return apply_filters( 'dgv_shortcode_output', $content, $video_id, $this->plugin );
	}

	/**
	 * The video page content
	 *
	 * @param $content
	 *
	 * @return mixed|string|void
	 */
	public function video_contents( $content ) {
		if ( is_singular( Database::POST_TYPE_UPLOADS ) ) {
			global $post;
			$child_theme_path  = get_stylesheet_directory();
			$parent_theme_path = get_template_directory();
			$theme_file_path   = trailingslashit( $child_theme_path !== $parent_theme_path ? $child_theme_path : $parent_theme_path );

			$override_paths = [
				$this->plugin->system()->views()->get_path( 'frontend/partials/single-content' ),
				$theme_file_path . 'wp-vimeo-videos/single-content.php',
				$theme_file_path . 'vimeify/single-content.php',
			];
			$override_paths = apply_filters( 'dgv_single_vimeo_content_path', $override_paths, $this->plugin );
			$override_paths = array_reverse( $override_paths );

			$found_path = null;
			foreach ( $override_paths as $override_path ) {
				if ( file_exists( $override_path ) ) {
					$found_path = $override_path;
					break;
				}
			}

			ob_start();
			include( $found_path );
			$content = ob_get_clean();
		}

		return $content;
	}
}