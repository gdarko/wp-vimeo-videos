<?php

namespace Vimeify\Core\Shared\Blocks;

use Vimeify\Core\Abstracts\BaseBlock;

class VideosTable extends BaseBlock {

	/**
	 * Registers block editor assets
	 * @return void
	 */
	public function register_block() {
		$block_path = $this->plugin->path() . 'blocks/dist/videos-table/';
		if ( ! file_exists( $block_path . 'index.asset.php' ) ) {
			return;
		}
		//$asset_file = include $block_path . 'index.asset.php';
		register_block_type( $block_path, array(
			'api_version'     => 3,
			//'editor_script'   => 'vimeify-videos-table-block-editor',
			'render_callback' => [ $this, 'render_block' ],
		) );
	}

	/**
	 * Registers block editor assets
	 * @return void
	 */
	public function register_block_editor_assets() {
		wp_register_style(
			'vimeify-videos-table-block-editor',
			$this->plugin->url() . 'blocks/dist/videos-table/index.css',
			array(),
			filemtime( $this->plugin->path() . 'blocks/dist/videos-table/index.css' )
		);
	}

	/**
	 * Dynamic render for the upload block
	 *
	 * @param $block_attributes
	 * @param $content
	 *
	 * @return string
	 */
	public function render_block( $block_attributes, $content ) {

		$params = [
            'posts_per_page' => ! empty($block_attributes['posts_per_page']) ? (int) $block_attributes['posts_per_page'] : 6,
            'author'         => ! empty($block_attributes['author']) && (int) $block_attributes['author'] >= 1 ? (int) $block_attributes['author'] : 'any',
            'categories'     => ! empty($block_attributes['categories']) && is_array($block_attributes['categories']) ? array_filter(array_map('intval', $block_attributes['categories']), function ($v) {
                return $v > 0;
            }) : [],
            'order'          => ! empty($block_attributes['order']) ? $block_attributes['order'] : 'desc',
            'order_by'       => ! empty($block_attributes['orderby']) ? (int) $block_attributes['orderby'] : 'date',
            'show_pagination' => ! ( isset( $block_attributes['show_pagination'] ) && 'no' === $block_attributes['show_pagination'] ),
		];

        $view = apply_filters( 'dgv_frontend_view_videos_table', null, $this->plugin );
		if ( is_null( $view ) ) {
			$view = new \Vimeify\Core\Frontend\Views\VideosTable( $this->plugin );
		}
		$view->enqueue();

		return $view->output( $params );
	}
}