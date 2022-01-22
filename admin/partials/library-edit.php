<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
/* @var WP_DGV_Settings_Helper $settings_helper */

$video_id    = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : null;
$vimeo_id    = $db_helper->get_vimeo_id( $video_id );
$permalink   = get_permalink( $video_id );
$front_pages = (int) $settings_helper->get( 'dgv_enable_single_pages' );
$vimeo_link  = $db_helper->get_vimeo_link( $video_id );

$video = array();
try {
	$video = $vimeo_helper->get_video_by_local_id( $video_id, array(
		'uri',
		'name',
		'description',
		'link',
		'duration',
		'width',
		'height',
		'is_playable',
		'privacy',
		'embed',
		'parent_folder',
		'upload'
	) );
} catch ( \Exception $e ) {

}

?>

<h2 class="wvv-mb-0"><?php echo get_the_title( sanitize_text_field( $_GET['id'] ) ); ?></h2>

<?php if ( $front_pages ) : ?>
    <div id="edit-slug-box" class="wvv-p-0">
        <strong><?php _e( 'Permalink:', 'wp-vimeo-videos' ); ?></strong>
        <span id="sample-permalink"><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_url($permalink); ?></a></span>
    </div>
<?php endif; ?>

<div class="wvv-row">
    <div class="wvv-col-40">
        <!-- Basic Information -->
        <div class="metabox-holder">
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle ui-sortable-handle"><?php _e( 'Preview Video', 'wp-vimeo-videos' ); ?></h2>
                </div>
                <div class="inside">
                    <div class="form-row">
						<?php echo do_shortcode( '[dgv_vimeo_video id="' . esc_attr( $vimeo_id ) . '"]' ); ?>
                    </div>
                    <div class="form-row">
                        <p class="wvv-mb-0"><a href="<?php echo esc_url( $vimeo_link ); ?>" class="button-primary"><?php _e( 'View On Vimeo', 'wp-vimeo-videos' ); ?></a></p>
                    </div>
                </div>
            </div>
        </div>

        <p>
            <small><em><?php echo sprintf( __( 'Privacy management, folder management, front-end upload and more options available in the %s.', 'wp-vimeo-videos' ), '<a href="' . wvv_get_purchase_url() . '" target="_blank">' . __( 'premium Version', 'wp-vimeo-videos' ) . '</a>' ); ?></em></small>
        </p>
    </div>
</div>
