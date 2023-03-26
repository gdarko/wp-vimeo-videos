<?php

namespace Vimeify\Core\Backend;

use Vimeify\Core\Abstracts\BaseProvider;

class Ui extends BaseProvider {

	const PAGE_VIMEO = 'dgv-library';
	const PAGE_SETTINGS = 'dgv-settings';


	public $screen_options;

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	public function register() {

		add_action( 'in_admin_header', [ $this, 'do_admin_notices' ], 50 );
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'add_meta_boxes', [ $this, 'register_media_library_upload_metabox' ] );
		add_filter( 'manage_media_columns', [ $this, 'manage_media_columns' ], 15, 1 );
		add_action( 'manage_media_custom_column', [ $this, 'manage_media_custom_column' ], 15, 2 );
		add_filter( 'plugin_action_links_' . $this->plugin->basename(), [ $this, 'plugin_action_links' ], 100, 1 );

		$this->screen_options = new \Vimeify\Core\Utilities\ScreenOptions(
			[
				self::PAGE_VIMEO => [
					'description'              => __( 'Show Description', 'wp-vimeo-videos-pro' ),
					'link_insteadof_shortcode' => __( 'Show Link instead of shortcode', 'wp-vimeo-videos-pro' ),
				]
			]
		);

	}

	/**
	 * Register the admin menus
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		add_media_page(
			__( 'Vimeo Library', 'wp-vimeo-videos-pro' ),
			'Vimeo',
			'upload_files',
			self::PAGE_VIMEO,
			array( $this, 'render_vimeo_page' )
		);
	}

	/**
	 * Renders the vimeo pages
	 */
	public function render_vimeo_page() {
		$this->plugin->system()->views()->render_view( 'admin/partials/library', [
			'plugin' => $this->plugin,
		] );
	}

	/**
	 * Unset third party notices.
	 */
	public function do_admin_notices() {
		if ( $this->is_any_page() ) {
			\remove_all_actions( 'admin_notices' );
		}
		do_action( 'dgv_admin_notices' );
		$this->instructions();
	}

	/**
	 * Registers the Media Library Integration Button
	 */
	public function register_media_library_upload_metabox() {
		if ( isset( $_GET['post'] ) && 'attachment' === get_post_type( $_GET['post'] ) ) {
			add_meta_box(
				'wvv_info_metabox_' . intval( $_GET['post'] ),
				__( 'WP Vimeo', 'wp-vimeo-videos-pro' ),
				array( $this, 'render_media_library_upload_metabox' ),
				null,
				'side'
			);
		}
	}

	/**
	 * Renders the Media Library Integration Button
	 */
	public function render_media_library_upload_metabox() {
		$attachment_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : null;
		if ( ! is_null( $attachment_id ) && 'attachment' === get_post_type( $attachment_id ) ) {

			echo '<div id="dgv-mlmb-' . esc_attr( $attachment_id ) . '">';

			echo $this->plugin->system()->views()->get_view( 'admin/partials/media-buttons', [
				'id'     => $attachment_id,
				'plugin' => $this->plugin,
			] );

			echo '</div>';
		}
	}

	/**
	 * Add WP Vimeo in the Media Library table
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function manage_media_columns( $columns ) {
		if ( $this->plugin->system()->vimeo()->is_connected ) {
			$columns['dgv_info'] = __( 'WP Vimeo', 'wp-vimeo-videos-pro' );
		}

		return $columns;
	}

	/**
	 * Render WP Vimeo in the Media Library table
	 *
	 * @param $column_name
	 * @param $attachment_id
	 */
	public function manage_media_custom_column( $column_name, $attachment_id ) {
		if ( $this->plugin->system()->vimeo()->is_connected ) {
			switch ( $column_name ) {
				case 'dgv_info':
					echo '<div id="dgv-mlmb-' . esc_attr( $attachment_id ) . '">';
					echo $this->plugin->system()->views()->get_view( 'admin/partials/media-buttons', [
						'id'     => $attachment_id,
						'plugin' => $this->plugin,
					] );
					echo '</div>';
					break;
			}
		}
	}

	/**
	 * Add instructions view
	 */
	public function instructions() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Verify dismiss
		$dismiss_key = 'dgv_instructions_dismissed';
		if ( isset( $_GET['wvv_dismiss_instructions'] ) && isset( $_GET['wvv_nonce'] ) ) {
			if ( wp_verify_nonce( $_GET['wvv_nonce'], 'wvv_instructions_dismiss' ) ) {
				update_option( $dismiss_key, 1 );
			}
		}
		// Render if not dismissed.
		$instructions_hidden = get_option( $dismiss_key );
		if ( ! $instructions_hidden || empty( $instructions_hidden ) || intval( $instructions_hidden ) !== 1 ) {
			$disallowed = array();
			$page       = isset( $_GET['page'] ) ? $_GET['page'] : null;
			if ( ! in_array( $page, $disallowed ) ) {
				$this->plugin->system()->views()->get_view( 'admin/partials/instructions' );
			}
		}
	}

	/**
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @param  array  $links  List of existing plugin action links.
	 *
	 * @return array         List of modified plugin action links.
	 *
	 */
	public function plugin_action_links( $links ) {
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/options-general.php?page=dgv-settings' ) ) . '">' . __( 'Settings',
				'wp-vimeo-videos-pro' ) . '</a>'
		), $links );

		return $links;
	}

	/**
	 * Is any page?
	 * @return bool
	 */
	public function is_any_page() {
		return is_admin() && isset( $_GET['page'] ) && in_array( $_GET['page'], array(
				self::PAGE_VIMEO,
				self::PAGE_SETTINGS
			) );
	}

	/**
	 * Is the list page?
	 * @return bool
	 */
	public function is_list_page() {
		return $this->is_any_page() && ! isset( $_GET['action'] );
	}

	/**
	 * Is the edit page?
	 * @return bool
	 */
	public function is_edit_page() {
		return $this->is_any_page() && isset( $_GET['action'] ) && 'edit' === $_GET['action'];
	}
}