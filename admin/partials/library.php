<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */

?>
<div class="wrap dgv-wrap">
	<?php
	if ( ! wvv_php_version_ok() ) {
		include 'outdated.php';
	} else {
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'new' ) {

			if ( $vimeo_helper->is_connected ) {
				if ( $vimeo_helper->can_upload() ) {
					include 'library-upload.php';
				} else {
					include 'not-allowed-upload.php';
				}
			} else {
				include 'not-connected.php';
			}

		} elseif ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['id'] ) ) {
			include 'library-edit.php';
		} elseif ( ! isset( $_GET['action'] ) ) {
			include 'library-list.php';
		} else {
			echo __( 'Invalid action', 'wp-vimeo-videos' );
		}
	}
	?>
</div>