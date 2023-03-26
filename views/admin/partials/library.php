<?php
/* @var \Vimeify\Core\Plugin $plugin */

$core_validator = new \Vimeify\Core\Utilities\Validators\CoreValidator();

?>
<div class="wrap dgv-wrap">
	<?php
	if ( ! $core_validator->is_version_met( $plugin->minimum_php_version() ) ) {
		include 'outdated.php';
	} else {

		if ( isset( $_GET['action'] ) && $_GET['action'] === 'new' ) {

			if ( $plugin->system()->vimeo()->is_connected ) {
				if ( $plugin->system()->vimeo()->can_upload() ) {
					include 'library-upload.php';
				} else {
					include 'not-allowed-upload.php';
				}
			} else {
				include 'not-connected.php';
			}

		} elseif ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['id'] ) ) {
			include 'library-edit.php';
		} elseif ( ( ! isset( $_GET['action'] ) || empty( $_GET['action'] ) ) || ( isset( $_GET['action'] ) && ( 'delete' === $_GET['action'] || - 1 === (int) $_GET['action'] ) ) ) {
			include 'library-list.php';

		} else {
			echo __( 'Invalid action', 'wp-vimeo-videos-pro' );
		}
	}
	?>
</div>
