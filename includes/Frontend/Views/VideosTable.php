<?php

namespace Vimeify\Core\Frontend\Views;

use Vimeify\Core\Abstracts\BaseView;
use Vimeify\Core\Abstracts\Interfaces\ViewInterface;
use Vimeify\Core\Components\Database;

class VideosTable extends BaseView {

	/**
	 * Enqueue the required styles
	 * @var string[]
	 */
	protected $styles = [ 'dgv-frontend-videos-table', 'dgv-iconfont' ];
	protected $page_query_key = 'page_number';

	/**
	 * Handles the output
	 * @return string
	 */
	protected function get_output() {

		$paged = isset( $_REQUEST[ $this->page_query_key ] ) ? (int) $_REQUEST[ $this->page_query_key ] : 1;

		$query_args = [
			'post_type'      => Database::POST_TYPE_UPLOADS,
			'orderby'        => $this->args['orderby'],
			'order'          => $this->args['order'],
			'posts_per_page' => $this->args['posts_per_page'],
			'paged'          => $paged,
			'author'         => ! empty( $this->args['author'] ) ? $this->args['author'] : 'any',
		];

		if ( ! empty( $this->args['category'] ) ) {
			$query_args['tax_query'] = [
				[
					'taxonomy' => Database::TAX_CATEGORY,
					'field'    => 'slug',
					'terms'    => $this->args['category'],
				]
			];
		}

		$query_args   = apply_filters( 'dgv_frontend_view_videos_table_query', $query_args );
		$loop_query   = new \WP_Query( $query_args );
		$single_pages = (int) $this->plugin->system()->settings()->get( 'frontend.behavior.enable_single_pages' );

		$_actions = [
			[
				'icon'      => 'vimeify-eye',
				'text'      => __( 'View', 'wp-vimeo-videos' ),
				'condition' => $single_pages,
				'action'    => function ( $entry ) {
					/* @var \WP_Post $entry */
					return [
						'link'   => get_permalink( $entry->ID ),
						'target' => '_self',
					];
				}
			],
		];

		$_actions = apply_filters( 'dgv_frontend_view_video_table_actions', $_actions );
		$actions  = [];
		foreach ( $_actions as $action ) {
			if ( isset( $action['condition'] ) ) {
				if ( $action['condition'] ) {
					$actions[] = $action;
				}
			} else {
				$actions[] = $action;
			}
		}

		return $this->plugin->system()->views()->get_view( 'frontend/partials/videos-table', [
			'query'        => $loop_query,
			'actions'      => $actions,
			'single_pages' => $this->plugin->system()->settings()->get( 'frontend.behavior.enable_single_pages' ),
			'pagination'   => $this->paginate( $loop_query ),
		] );

	}

	/**
	 * Set the defaults
	 *
	 * @param array $args
	 */
	public function set_defaults() {
		$this->defaults = [
			'orderby'        => 'date',
			'order'          => 'desc',
			'posts_per_page' => 3,
			'category'       => '',
			'author'         => 'any',
		];
	}


	/**
	 * Paginate the query
	 *
	 * @param \WP_Query $query
	 * @param $pagerange
	 *
	 * @return string
	 */
	protected function paginate( $query, $pagerange = 2 ) {

		if ( 'paged' === $this->page_query_key ) {
			$paged = get_query_var( $this->page_query_key ) ? get_query_var( $this->page_query_key ) : 1;
		} else {
			$paged = isset( $_GET[ $this->page_query_key ] ) ? (int) $_GET[ $this->page_query_key ] : 1;
		}

		$url = strtok( get_pagenum_link( 1 ), '?' );

		$pagination_args = array(
			'base'         => $url . '%_%',
			'format'       => '?' . $this->page_query_key . '=%#%',
			'total'        => $query->max_num_pages,
			'current'      => $paged,
			'show_all'     => false,
			'end_size'     => 1,
			'mid_size'     => $pagerange,
			'prev_next'    => true,
			'prev_text'    => __( '«' ),
			'next_text'    => __( '»' ),
			'type'         => 'array',
			'add_args'     => false,
			'add_fragment' => ''
		);

		$paginate_links = paginate_links( $pagination_args );

		$output = '';
		if ( is_array( $paginate_links ) ) {
			$output .= "<div class='dgv-table-pagination'>";
			$output .= '<ul class="dgv-table-pagination-list">';
			foreach ( $paginate_links as $i => $page ) {
				$output .= "<li>$page</li>";
			}
			$output .= '</ul>';
			$output .= "</div>";
		}

		return $output;
	}

	/**
	 * Return the users
	 * @return array
	 */
	public function get_authors() {

		$args = apply_filters( 'dgv_view_videos_table_get_users_args', [
			'role__in' => [ 'administrator', 'editor', 'author', 'contributor' ],
			'number'   => 200,
			'orderby'  => 'name',
			'order'    => 'asc',
		] );

		$data  = [];
		$users = get_users( $args );
		foreach ( $users as $item ) {
			$data[ $item->ID ] = ! empty( $item->display_name ) ? $item->display_name : $item->user_email;
		}

		return apply_filters( 'dgv_view_videos_table_get_users', $data );
	}

	/**
	 * Return the terms
	 * @return array
	 */
	public function get_categories() {

		$args = apply_filters( 'dgv_view_videos_table_get_categories_args', [
			'taxonomy' => Database::TAX_CATEGORY,
			'number'   => 200,
			'orderby'  => 'name',
			'order'    => 'asc',
		] );

		$data  = [];
		$terms = get_terms( $args );
		foreach ( $terms as $item ) {
			$data[ $item->term_id ] = $item->name;
		}

		return apply_filters( 'dgv_view_videos_table_get_categories', $data );
	}
}