<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<h2><?php _e( 'Vimeo Videos', 'wp-vimeo-videos' ); ?>

    <a href="<?php echo admin_url( 'upload.php?page=' . WP_DGV_Admin::PAGE_VIMEO . '&action=new' ); ?>"
       class="page-title-action"><?php _e( 'Upload new', 'wp-vimeo-videos' ); ?></a>

	<?php if ( current_user_can( 'manage_options' ) ): ?>
        <a href="<?php echo admin_url( 'options-general.php?page=' . WP_DGV_Admin::PAGE_SETTINGS . '&action=settings' ); ?>"
           class="page-title-action"><?php _e( 'Settings', 'wp-vimeo-videos' ); ?></a>
	<?php endif; ?>

</h2>

<form method="post">

    <input type="hidden" name="page" value="ttest_list_table">

	<?php

	$list_table = new WP_DGV_List_Table();

	$list_table->prepare_items();

	//$list_table->search_box( 'search', 'search_id' ); //TODO

	$list_table->display();

	?>

</form>