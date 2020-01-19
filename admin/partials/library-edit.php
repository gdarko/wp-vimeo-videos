<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<h2><?php echo get_the_title( $_GET['id'] ); ?></h2>

<?php
$vimeo_id = $db_helper->get_vimeo_id( $_GET['id'] );
?>

<div class="wvv-row">
    <div class="wvv-col-40">
        <!-- Basic Information -->
        <div class="metabox-holder">
            <div class="postbox">
                <h2 class="hndle ui-sortable-handle"><?php _e( 'Basic Information', 'wp-vimeo-videos' ); ?></h2>
                <div class="inside">
                    <div class="form-row wvv-mt-20">
						<?php echo do_shortcode( '[dgv_vimeo_video id="' . $vimeo_id . '"]' ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>