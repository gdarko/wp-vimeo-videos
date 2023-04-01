<?php

namespace Vimeify\Core\Backend\ListTables;

use Vimeify\Core\Plugin;
use Vimeify\Core\Utilities\Formatters\ByteFormatter;
use Vimeify\Core\Utilities\Formatters\WPFormatter;
use Vimeify\Core\Utilities\ScreenOptions;
use Vimeify\Core\Utilities\TableFlash;
use Vimeify\Core\Backend\Ui;

class VideosTable extends \WP_List_Table {

	/**
	 * Is authors only
	 * @var int
	 */
	protected $author_uploads_only;

	/**
	 * @var TableFlash
	 */
	protected $flash;

	/**
	 * Enable or disable local pages for videos
	 * @var bool
	 */
	protected $front_pages;

	/**
	 * Thumbnails support
	 * @var
	 */
	protected $thumbs_support;

	/**
	 * The log tag
	 * @var string
	 */
	protected $logtag = 'DGV-ADMIN-LIST';

	/**
	 * Has description enabled
	 * @var bool
	 */
	protected $show_description = false;

	/**
	 * Show link instead of shortcode
	 * @var bool
	 */
	protected $show_link = false;


	/**
	 * The system interface
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * VideosTable constructor.
	 */
	public function __construct( Plugin $plugin ) {
        
        $this->plugin  = $plugin;
        
		parent::__construct( array(
			'singular' => 'video',
			'plural'   => 'videos',
			'ajax'     => false
		) );
		$this->author_uploads_only = (int) $this->plugin->system()->settings()->get( 'admin.videos_list_table.show_author_uploads_only' );

		$user = wp_get_current_user();
		if ( isset( $user->roles ) && in_array( 'administrator', (array) $user->roles ) ) {
			$this->author_uploads_only = 0;
		}

		$this->flash            = new TableFlash;
		$this->show_description = ScreenOptions::get_option( 'description' ) === 'on' ? true : false;
		$this->show_link        = ScreenOptions::get_option( 'link_insteadof_shortcode' ) === 'on' ? true : false;

		$this->front_pages    = (int) $this->plugin->system()->settings()->get( 'frontend.behavior.enable_single_pages' );
		$this->thumbs_support = (int) $this->plugin->system()->settings()->get( 'admin.videos_thumbnails.enable_thumbnails' );
	}


	public function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Message to show if no designation found
	 *
	 * @return void
	 */
	public function no_items() {
		_e( 'No videos found', 'wp-vimeo-videos-pro' );
	}

	/**
	 * Default column values if no callback found
	 *
	 * @param  object  $item
	 * @param  string  $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'embed':
				if ( ! $this->show_link ) {
					$vimeo_id = $this->plugin->system()->database()->get_vimeo_id( $item->ID );

					return '<code class="embed-code">[dgv_vimeo_video id="' . $vimeo_id . '"]</code>' . sprintf( '<span class="dgv-copy-embed-code dashicons dashicons-admin-links" title="%s"></span>', __( 'Copy to clipboard', 'wp-vimeo-videos-pro' ) );
				} else {
					$link = $this->plugin->system()->database()->get_vimeo_link( $item->ID );

					return sprintf( '<code class="embed-code">%s</code> <span class="dgv-copy-embed-code dashicons dashicons-admin-links" title="%s"></span>', $link, __( 'Copy to clipboard', 'wp-vimeo-videos-pro' ) );
				}
			case 'author':
                $wp_formatter = new WPFormatter();
				return $wp_formatter->get_user_edit_link( $item->post_author );
			case 'size':
				$size = get_post_meta( $item->ID, 'dgv_size', true );
				return is_numeric( $size ) ? (new ByteFormatter())->format( $size ) : __( 'Not available' );
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
		$columns                = array(
			'cb' => '<input type="checkbox" />',
		);
		$columns['title']       = __( 'Title', 'wp-vimeo-videos-pro' );
		$columns['embed']       = __( 'Embed', 'wp-vimeo-videos-pro' );
		$columns['author']      = __( 'Author', 'wp-vimeo-videos-pro' );
		$columns['size']        = __( 'Size', 'wp-vimeo-videos-pro' );
		$columns['uploaded_at'] = __( 'Uploaded', 'wp-vimeo-videos-pro' );

		return $columns;
	}

	/**
	 * Render the designation name column
	 *
	 * @param  object  $item
	 *
	 * @return string
	 */
	public function column_title( $item ) {

		$actions    = array();
		$can_delete = $this->plugin->system()->vimeo()->can_delete() ? 1 : 0;
		$vimeo_uri  = $this->plugin->system()->database()->get_vimeo_uri( $item->ID );
		$vimeo_id   = $this->plugin->system()->database()->get_vimeo_id( $item->ID );
		$url_vimeo  = $this->plugin->system()->database()->get_vimeo_link( $item->ID );
		$url_edit   = admin_url( 'admin.php?page=' . Ui::PAGE_VIMEO . '&action=edit&id=' . $item->ID );
		$url_local  = get_permalink( $item->ID );

		$actions['edit']  = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $url_edit, $item->ID, __( 'Manage this video', 'wp-vimeo-videos-pro' ), __( 'Manage', 'wp-vimeo-videos-pro' ) );
		$actions['vimeo'] = sprintf( '<a href="%s" target="_blank" data-id="%d" title="%s">%s</a>', $url_vimeo, $item->ID, __( 'Vimeo video link', 'wp-vimeo-videos-pro' ), __( 'Vimeo Link', 'wp-vimeo-videos-pro' ) );
		if ( $this->front_pages ) {
			$actions['view'] = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $url_local, $item->ID, __( 'View the video on the front-end', 'wp-vimeo-videos-pro' ), __( 'View', 'wp-vimeo-videos-pro' ) );
		}

		$delete_cap = apply_filters( 'dgv_vimeo_cap_delete', 'delete_posts' );
		if ( current_user_can( $delete_cap ) ) {
			$actions['delete'] = sprintf( '<a href="#" class="submitdelete dg-vimeo-delete" data-vimeo-uri="%s"  data-can-delete="%d" data-id="%d" title="%s">%s</a>', $vimeo_uri, $item->ID, $can_delete, __( 'Delete video from vimeo', 'wp-vimeo-videos-pro' ), __( 'Delete', 'wp-vimeo-videos-pro' ) );
		}

		$thumbnail       = '';
		$thumbnail_class = '';

		if ( ! empty( $vimeo_id ) && $this->thumbs_support ) {
			$thumb_url = $this->plugin->system()->vimeo()->get_thumbnail( $vimeo_id );
			if ( ! empty( $thumb_url ) ) {
				$thumbnail_class = 'wvv-thumb-link';
				$thumbnail       = '<img src="' . esc_url( $thumb_url ) . '"/>';
			}
		}

		$description = '';
		if ( $this->show_description ) {
			$description = wp_trim_words( $item->post_content, 30 );
		}

		return sprintf(
			'<a class="%s" href="%s">%s<strong>%s</strong></a> %s %s',
			esc_attr( $thumbnail_class ),
			esc_url( $url_edit ),
			$thumbnail,
			esc_html( $item->post_title ),
			esc_html( $description ),
			$this->row_actions( $actions )
		);
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
		$actions = array(
			'delete' => __( 'Delete', 'wp-vimeo-videos-pro' ),
		);

		return $actions;
	}

	/**
	 * Render the checkbox column
	 *
	 * @param  object  $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="record_id[]" value="%d" />', $item->ID
		);
	}

	/**
	 * The views
	 * @return array
	 */
	protected function get_views() {
		return [
			'local' => sprintf( '<a class="current" aria-current="page" href="%s">%s</a>', add_query_arg( [ 'type' => 'local' ], admin_url( 'admin.php?page=' . Ui::PAGE_VIMEO ) ), __( 'Local', 'wp-vimeo-videos-pro' ) ),
			'vimeo' => sprintf( '<a class="disabled" title="%s" href="#">%s</a>', __( 'Feature coming soon!', 'wp-vimeo-videos-pro' ), __( 'Vimeo', 'wp-vimeo-videos-pro' ) ),
		];
	}

	/**
	 * Prepare the class items
	 *
	 * @return void
	 */
	public function prepare_items() {

		$this->process_bulk_actions();

		global $wp_query;

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

		if ( $this->author_uploads_only && ! current_user_can( 'administrator' ) ) {
			$args['author'] = get_current_user_id();
		} else {
			$filter_author_ID = isset( $_REQUEST['author'] ) ? $_REQUEST['author'] : 0;
			if ( $filter_author_ID > 0 ) {
				$args['author'] = $filter_author_ID;
			}
		}

		if ( ! empty( $_REQUEST['s'] ) ) {
			$args['s'] = sanitize_text_field( $_REQUEST['s'] );
		}

		if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
			$args['orderby'] = sanitize_text_field( $_REQUEST['orderby'] );
			$args['order']   = sanitize_text_field( $_REQUEST['order'] );
		}
		$wp_query    = $this->plugin->system()->database()->get_videos( $args, 'query' );
		$this->items = $wp_query->get_posts();

		$this->set_pagination_args( array(
			'total_items' => $wp_query->found_posts,
			'per_page'    => $per_page,
		) );
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @param  string  $which
	 *
	 * @since 3.1.0
	 *
	 */
	protected function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			?>

			<?php if ( ! $this->author_uploads_only ): ?>
                <div class="alignleft actions bulkactions">
					<?php
					$filter_author    = false;
					$filter_author_ID = isset( $_REQUEST['author'] ) ? intval( $_REQUEST['author'] ) : 0;
					if ( $filter_author_ID ) {
						$filter_author = get_user_by( 'id', $filter_author_ID );
					}
					?>
                    <div class="alignleft actions">
                        <label class="screen-reader-text" for="author"><?php __( 'Filter by author', 'wp-vimeo-videos-pro' ); ?></label>
                        <select name="author" id="author" class="postform dgv-select2 dgv-select2-clearable" data-action="dgv_user_search" data-placeholder="<?php _e( 'Filter by author', 'wp-vimeo-videos-pro' ); ?>">
							<?php if ( ! empty( $filter_author ) ): ?>
                                <option selected value="<?php echo esc_attr( $filter_author->ID ); ?>"><?php echo esc_html( $filter_author->display_name ); ?></option>
							<?php endif; ?>
                        </select>
                        <button type="submit" name="filter_action" id="post-query-submit" value="Filter" class="button"><?php _e( 'Filter', 'wp-vimeo-videos-pro' ); ?></button>
                        <a href="" class="dgv-clear-selection" data-target=".dgv-select2-clearable" style="<?php echo $filter_author ? '' : 'display:none;'; ?>"><?php _e( 'Clear', 'wp-vimeo-videos-pro' ); ?></a>
                    </div>
                </div>
			<?php endif; ?>

			<?php
		}
	}

	/**
	 * Process the bulk actions
	 */
	protected function process_bulk_actions() {

		$capabilities = array( 'delete' => apply_filters( 'dgv_vimeo_cap_delete', 'delete_posts' ) );

		// security check!
		if ( isset( $_REQUEST['_wpnonce'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
			$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : null;
			$action = 'bulk-' . $this->_args['plural'];
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( "Permission denied. Security check failed.", 'wp-vimeo-videos-pro' );
			}
			$capability = isset( $capabilities[ $action ] ) ? $capabilities[ $action ] : null;
			if ( ( $action && 'bulk-videos' !== $action ) && ! current_user_can( $capability ) ) {
				wp_die( "Permission denied. You don't have access to perform DELETE on this resource.", 'wp-vimeo-videos-pro' );
			}
		}

		$action = $this->current_action();
		$countV = 0;
		$countD = 0;
		switch ( $action ) {
			case 'delete':
				if ( ! $this->plugin->system()->vimeo()->can_delete() ) {
					$signature = $this->flash->flash_message( __( 'Unable to delete video(s). The required Vimeo API scope "delete" is missing.', 'wp-vimeo-videos-pro' ), 'error' );
					wp_redirect( $this->flash->get_current_url( $signature ) );
					exit;
				}
				$records = isset( $_REQUEST['record_id'] ) ? (array) $_REQUEST['record_id'] : array();
				foreach ( $records as $record ) {
					$vimeo_uri = $this->plugin->system()->database()->get_vimeo_uri( $record );
					if ( ! empty( $vimeo_uri ) ) {

						try {
							$response = $this->plugin->system()->vimeo()->delete( $vimeo_uri );
							usleep( 100000 );
							$this->plugin->system()->logger()->log( 'Delete response: ', $this->logtag );
							$this->plugin->system()->logger()->log( $response, $this->logtag );
							$this->plugin->system()->logger()->log( sprintf( 'Remote video %s deleted', $vimeo_uri ), $this->logtag );
							$countV ++;
						} catch ( \Vimeo\Exceptions\VimeoRequestException $e ) {
							$this->plugin->system()->logger()->log( sprintf( 'Unable to delete remote video %s. (Error: %s)', $vimeo_uri, $e->getMessage() ), $this->logtag );
						}
					}
					if ( wp_delete_post( $record, true ) ) {
						$this->plugin->system()->logger()->log( sprintf( 'Local video %s deleted', $record ), $this->logtag );
						$countD ++;
					}
				}
				if ( $countD > 0 || $countV > 0 ) {
					$signature = $this->flash->flash_message( __( 'Video(s) deleted successfully.', 'wp-vimeo-videos-pro' ), 'success' );
				} else {
					$signature = $this->flash->flash_message( __( 'Unable to delete video(s)', 'wp-vimeo-videos-pro' ), 'error' );
				}

				$url = $this->flash->get_current_url( $signature );
				wp_redirect( $url );
				die;
		}

		return;

	}
}