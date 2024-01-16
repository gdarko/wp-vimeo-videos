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

namespace Vimeify\Core\Frontend;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Components\Database;
use Vimeify\Core\Frontend\Views\Video;
use Vimeify\Core\Frontend\Views\VideosTable;

class Hooks extends BaseProvider {

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {
		add_shortcode( 'vimeo_video', array( $this, 'shortcode_video' ) ); // DEPRECATED.
		add_shortcode( 'dgv_vimeo_video', array( $this, 'shortcode_video' ) ); // DEPRECATED.
		add_shortcode( 'vimeify_video', array( $this, 'shortcode_video' ) );
		add_shortcode( 'vimeify_videos_table', array( $this, 'shortcode_videos_table' ) );
		add_filter( 'the_content', [ $this, 'video_contents' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 15 );
	}

	/**
	 * The video shortcode
	 *
	 * @param $atts
	 *
	 * @return false|string
	 */
	public function shortcode_video( $atts ) {
		$view = apply_filters( 'dgv_frontend_view_video', null, $this->plugin );
		if ( is_null( $view ) ) {
			$view = new Video( $this->plugin );
		}
		$view->enqueue();
		$atts = shortcode_atts( $view->get_defaults(), $atts );

		return $view->output( $atts );
	}

	/**
	 * The videos table shortcode
	 * @return string
	 */
	public function shortcode_videos_table( $atts ) {
		$view = apply_filters( 'dgv_frontend_view_videos_table', null, $this->plugin );
		if ( is_null( $view ) ) {
			$view = new VideosTable( $this->plugin );
		}
		$view->enqueue();
		$atts = shortcode_atts( $view->get_defaults(), $atts );

		return $view->output( $atts );
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

	/**
	 * Enqueue the scripts
	 * @return void
	 */
	public function enqueue_scripts() {

		global $post;

		$is_post = $post instanceof \WP_Post;

		if ( is_singular( Database::POST_TYPE_UPLOADS ) || ( $is_post && has_shortcode( $post->post_content, 'vimeify_video' ) ) ) {
			$video = new Video( $this->plugin );
			$video->enqueue();
		}

		if ( $is_post && has_shortcode( $post->post_content, 'vimeify_videos_table' ) ) {
			$table = new VideosTable( $this->plugin );
			$table->enqueue();
		}

	}
}