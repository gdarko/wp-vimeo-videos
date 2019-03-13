<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class DGV_Videos_Table extends \WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'singular' => 'video',
			'plural'   => 'videos',
			'ajax'     => false
		) );
	}

	function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Message to show if no designation found
	 *
	 * @return void
	 */
	function no_items() {
		_e( 'No videos found', 'dg-vimeo-videos' );
	}

	/**
	 * Default column values if no callback found
	 *
	 * @param  object $item
	 * @param  string $column_name
	 *
	 * @return string
	 */
	function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'title':
				return $item->post_title;
			case 'privacy':
				$privacy = get_post_meta( $item->ID, 'dgv_privacy', true );
				if ( isset( $privacy['view'] ) ) {
					return '<code>' . $privacy['view'] . '</code>';
				} else {
					return 'unknown';
				}
			case 'embed':
				$vimeo_id = dgv_get_vimeo_id($item->ID);
				return '<code>[vimeo_video id="' . $vimeo_id . '"]</code>';
			case 'uploaded_at':
				return get_the_date( get_option( 'date_format' ), $item );
			default:
				return isset( $item->$column_name ) ? $item->$column_name : '';
		}
	}

	/**
	 * Get the column names
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'title'       => __( 'Title', 'dg-vimeo-videos' ),
			'privacy'     => __( 'Privacy', 'dg-vimeo-videos' ),
			'embed'       => __( 'Embed', 'dg-vimeo-videos' ),
			'uploaded_at' => __( 'Uploaded', 'dg-vimeo-videos' ),

		);

		return $columns;
	}

	/**
	 * Render the designation name column
	 *
	 * @param  object $item
	 *
	 * @return string
	 */
	function column_title( $item ) {

		$actions = array();
		//$actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=vimeo-videos&action=edit&id=' . $item->id ), $item->id, __( 'Edit this item', 'dg-vimeo-videos' ), __( 'Edit', 'dg-vimeo-videos' ) );
		//$actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=vimeo-videos&action=delete&id=' . $item->id ), $item->id, __( 'Delete this item', 'dg-vimeo-videos' ), __( 'Delete', 'dg-vimeo-videos' ) );

		return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'upload.php?page=' . DGV_Backend::PAGE_HANDLE . '&action=edit&id=' . $item->ID ), $item->post_title, $this->row_actions( $actions ) );
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(//'name' => array( 'name', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Set the bulk actions
	 *
	 * @return array
	 */
	function get_bulk_actions() {
		$actions = array(//'trash'  => __( 'Move to Trash', 'dg-vimeo-videos' ),
		);

		return $actions;
	}

	/**
	 * Render the checkbox column
	 *
	 * @param  object $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="video_id[]" value="%d" />', $item->id
		);
	}

	/**
	 * Set the views
	 *
	 * @return array
	 */
	public function get_views_() {
		$status_links = array();
		$base_link    = admin_url( 'upload.php?page=' . DGV_Backend::PAGE_HANDLE . '&upload_new=1' );

		foreach ( $this->counts as $key => $value ) {
			$class                = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
			$status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
		}

		return $status_links;
	}

	/**
	 * Prepare the class items
	 *
	 * @return void
	 */
	function prepare_items() {

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page          = 20;
		$current_page      = $this->get_pagenum();
		$offset            = ( $current_page - 1 ) * $per_page;
		$this->page_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

		// only ncessary because we have sample data
		$args = array(
			'offset' => $offset,
			'number' => $per_page,
		);

		if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
			$args['orderby'] = $_REQUEST['orderby'];
			$args['order']   = $_REQUEST['order'];
		}

		$this->items = dgv_videos_get( $args );

		$this->set_pagination_args( array(
			'total_items' => dgv_videos_count(),
			'per_page'    => $per_page
		) );
	}
}