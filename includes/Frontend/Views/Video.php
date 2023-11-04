<?php

namespace Vimeify\Core\Frontend\Views;

use Vimeify\Core\Abstracts\BaseView;
use Vimeo\Exceptions\VimeoRequestException;

class Video extends BaseView {

	protected $styles = [ 'dgv-frontend-video' ];
	protected $scripts = [ 'dgv-frontend-video' ];

	/**
	 * Set the defaults
	 *
	 * @param array $args
	 */
	function set_defaults() {
		$this->defaults = [
			'id' => '',
		];
	}

	/**
	 * Handles the output
	 * @return string
	 * @throws VimeoRequestException
	 */
	protected function get_output() {

		if ( ! empty( $this->args['post_id'] ) ) {
			$video_id = $this->args['post_id'];
			$vimeo_id = $this->plugin->system()->database()->get_vimeo_id( $video_id );
		} else {
			$vimeo_id = $this->args['id'];
			$video_id = $this->plugin->system()->database()->get_post_id( $vimeo_id );
		}

		$embed_url = $this->get_embed_url( $vimeo_id, $video_id );

		$pre_output = apply_filters( 'dgv_frontend_preoutput_video', null, $vimeo_id, $this->plugin );
		$pre_output = apply_filters_deprecated( 'dgv_shortcode_pre_output', $pre_output, $video_id, $this->plugin );
		if ( ! is_null( $pre_output ) ) {
			$output = $pre_output;
		} else {
			$data = array(
				'vimeo_id'  => $vimeo_id,
				'thumbnail' => $this->plugin->system()->vimeo()->get_thumbnail( $vimeo_id, 'large' )
			);
			if ( ! empty( $embed_url ) ) {
				$data['embed_url'] = $embed_url;
			}
			$output = $this->plugin->system()->views()->get_view( 'frontend/partials/video', $data );
		}

		$output = apply_filters( 'dgv_frontend_output_video', $output, $video_id, $this->plugin );

		return apply_filters_deprecated( 'dgv_shortcode_output', [ $output, $video_id, $this->plugin ], '2.0.0' );
	}

	/**
	 * Return the embed url
	 *
	 * @param $vimeo_id
	 * @param $video_id
	 *
	 * @return string|void
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 */
	protected function get_embed_url( $vimeo_id, $video_id ) {
		if ( empty( $video_id ) ) {
			return '';
		}
		$embed_url = get_post_meta( $video_id, 'dgv_embed_link', true );
		if ( empty( $embed_url ) ) {
			try {
				$result = $this->plugin->system()->vimeo()->get( '/videos/' . $vimeo_id );
				if ( ! empty( $result['body'] ) ) {
					$this->plugin->system()->vimeo()->set_local_video_metadata( $video_id, $result['body'] );
					$embed_url = $result['body']['player_embed_url'];

				}
			} catch ( \Exception $e ) {
			}
		}

		return $embed_url;
	}
}