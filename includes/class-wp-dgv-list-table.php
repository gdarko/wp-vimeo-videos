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

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class WP_DGV_List_Table
 *
 * Responsible for displaying the admin table
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV_List_Table extends \WP_List_Table {

    protected $author_uploads_only;

	/**
	 * @var WP_DGV_Db_Helper
	 */
	protected $db_helper;

	/**
	 * @var WP_DGV_Api_Helper
	 */
	protected $api_helper;

    /**
     * @var WP_DGV_Settings_Helper
     */
	protected $settings_helper;

    /**
     * WP_DGV_List_Table constructor.
     */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'video',
			'plural'   => 'videos',
			'ajax'     => false
		) );

		$this->settings_helper = new WP_DGV_Settings_Helper();
		$this->author_uploads_only = $this->settings_helper->get('dgv_author_uploads_only');
		$this->db_helper  = new WP_DGV_Db_Helper();
		$this->api_helper = new WP_DGV_Api_Helper();
	}

    /**
     * @return array|string[]
     */
	public function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Message to show if no designation found
	 *
	 * @return void
	 */
	public function no_items() {
		_e( 'No videos found', 'wp-vimeo-videos' );
	}

	/**
	 * Default column values if no callback found
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {


		switch ( $column_name ) {
			case 'embed':
				$vimeo_id = $this->db_helper->get_vimeo_id( $item->ID );
				return '<code>[dgv_vimeo_video id="' . $vimeo_id . '"]</code>';
			case 'author':
				$user      = get_user_by( 'id', $item->post_author );
				$user_name = ($user instanceof WP_User && $user->exists()) ? $user->display_name : 'Unknown';
				return $user_name;
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
	public function get_columns() {
		$columns = array(
			//'cb'          => '<input type="checkbox" />',
			'title'       => __( 'Title', 'wp-vimeo-videos' ),
			'embed'       => __( 'Embed', 'wp-vimeo-videos' ),
			'author'      => __( 'Author', 'wp-vimeo-videos' ),
			'uploaded_at' => __( 'Uploaded', 'wp-vimeo-videos' ),
		);

		return $columns;
	}

	/**
	 * Render the designation name column
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_title( $item ) {
		$actions           = array();
		$url               = admin_url( 'upload.php?page=' . WP_DGV_Admin::PAGE_VIMEO . '&action=edit&id=' . $item->ID );
		$vimeo_uri  = $this->db_helper->get_vimeo_uri($item->ID);
		$vimeo_id   = $this->db_helper->get_vimeo_id($item->ID);
		$vimeo_link       = 'https://vimeo.com/'.$vimeo_id;
		$actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $url, $item->ID, __( 'Manage this video', 'wp-vimeo-videos' ), __( 'Manage', 'wp-vimeo-videos' ) );
		$actions['vimeo'] = sprintf('<a href="%s" target="_blank" data-id="%d" title="%s">%s</a>', $vimeo_link, $item->ID, __('Vimeo video link', 'wp-vimeo-videos'), __('Vimeo Link', 'wp-vimeo-videos'));

		return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', $url, $item->post_title, $this->row_actions( $actions ) );
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();

		return $sortable_columns;
	}

	/**
	 * Set the bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(//'trash'  => __( 'Move to Trash', 'wp-vimeo-videos' ),
		);

		return $actions;
	}

	/**
	 * Render the checkbox column
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
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
		$base_link    = admin_url( 'upload.php?page=' . WP_DGV_Admin::PAGE_VIMEO . '&upload_new=1' );
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
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$per_page              = 20;
		$current_page          = $this->get_pagenum();
		$offset                = ( $current_page - 1 ) * $per_page;
		$this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

		$args = array(
			'offset' => $offset,
			'number' => $per_page,
		);
		if($this->author_uploads_only && !current_user_can('administrator')) {
			$args['author'] = get_current_user_id();
		} else {
			$filter_author_ID = isset($_POST['author']) ? $_POST['author'] : 0;
			if($filter_author_ID > 0) {
				$args['author'] = $filter_author_ID;
			}
		}
		if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
			$args['orderby'] = $_REQUEST['orderby'];
			$args['order']   = $_REQUEST['order'];
		}
		$this->items = $this->db_helper->get_videos( $args );

		$this->set_pagination_args( array(
			'total_items' => $this->db_helper->get_videos_count(),
			'per_page'    => $per_page
		) );
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {

		if($this->author_uploads_only && !current_user_can('administrator')) {
			return;
		}

		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
		<?php
	}

    /**
     * Extra controls to be displayed between bulk actions and pagination
     *
     * @since 3.1.0
     *
     * @param string $which
     */
    protected function extra_tablenav( $which ) {
        if ( $which == "top" ) {
            ?>
            <div class="alignleft actions bulkactions">
                <?php
                $filter_author = false;
                $filter_author_ID = isset($_REQUEST['author']) ? intval($_REQUEST['author']) : 0;
                if($filter_author_ID) {
                    $filter_author = get_user_by('id', $filter_author_ID);
                }
                ?>
                <div class="alignleft actions">
                    <label class="screen-reader-text" for="author"><?php __('Filter by author', 'wp-vimeo-videos'); ?></label>
                    <select name="author" id="author" class="postform dgv-select2" data-placeholder="<?php _e('Filter by author'); ?>">
                        <?php if(!empty($filter_author)): ?>
                            <option selected value="<?php echo $filter_author->ID; ?>"><?php echo $filter_author->display_name; ?></option>
                        <?php endif; ?>
                    </select>
                    <input type="submit" name="filter_action" id="post-query-submit" class="button-primary" value="Filter">
                    <a href="" class="dgv-clear-selection" data-target=".dgv-select2" style="<?php echo $filter_author ? '' : 'display:none;'; ?>"><?php _e('Clear'); ?></a>
                </div>
            </div>
            <?php
        }
    }
}