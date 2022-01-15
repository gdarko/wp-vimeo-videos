<?php
// Urls
$url_guide    = wvv_get_guide_url();
$url_purchase = wvv_get_purchase_url();

// Dismiss Init
$nonce        = wp_create_nonce( 'wvv_instructions_dismiss' );
$actual_link  = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$dismiss_link = add_query_arg( 'wvv_nonce', $nonce, $actual_link );
$dismiss_link = add_query_arg( 'wvv_dismiss_instructions', 1, $dismiss_link );
?>
<div class="instructions dgv-instructions notice">
	<div class="dgv-instructions-card dgv-instructions-card-shadow">
		<div class="dgv-instructions-row dgv-instructions-header">
			<div class="dgv-instructions-colf">
				<p class="lead"><?Php _e( 'Thanks for installing', 'wp-vimeo-videos' ); ?> <strong
						class="green"><?php _e( 'Video Uploads for Vimeo', 'wp-vimeo-videos' ); ?></strong></p>
				<p class="desc"><?php echo sprintf( __( 'This plugin allows you to easily upload and embed Vimeo videos through your WordPress website.', 'wp-vimeo-videos' ) ); ?></p>
				<p class="desc"><?php echo sprintf( __( 'To %s please follow the steps below:', 'wp-vimeo-videos' ), '<strong>' . __( 'get started', 'wp-vimeo-videos' ) . '</strong>' ); ?></p>
			</div>
		</div>
		<div class="dgv-instructions-row">
			<div class="dgv-instructions-col4">
				<div class="dgv-instructions-instruction">
					<h4 class="navy"><?php _e( '1. Vimeo Developer Portal', 'wp-vimeo-videos' ); ?></h4>
					<p>
						<?php
						$txt_create_app = '<strong>' . __( 'Create App', 'wp-vimeo-videos' ) . '</strong>';
						$txt_dev_portal = '<a target="_blank" href="https://developer.vimeo.com/">' . __( 'Vimeo Developer Portal', 'wp-vimeo-videos' ) . '</a>';
						echo sprintf( __( 'To get started and successfully connect the plugin to Vimeo you will need to sign up at %s and then %s that will be used by your website.', 'wp-vimeo-videos' ), $txt_dev_portal, $txt_create_app );
						?>
					</p>
				</div>
			</div>
			<div class="dgv-instructions-col4">
				<div class="dgv-instructions-instruction">
					<h4 class="navy"><?php _e( '2. Request Upload Access', 'wp-vimeo-videos' ); ?></h4>
					<p>
						<?php _e( 'In order to be able to upload videos from external software like this plugin you need to request Upload Access from Vimeo and wait for approval.', 'wp-vimeo-videos' ); ?><br/>
						<strong><?php _e( '(1-5 days required for approval)', 'wp-vimeo-videos' ); ?></strong>
					</p>
				</div>
			</div>
			<div class="dgv-instructions-col4">
				<div class="dgv-instructions-instruction">
					<h4 class="navy"><?php _e( '3. Obtain API Credentials', 'wp-vimeo-videos' ); ?></h4>
					<p>
						<?php
						$txt_c_id  = '<strong>' . __( 'Client ID', 'wp-vimeo-videos' ) . '</strong>';
						$txt_c_sec = '<strong>' . __( 'Client Secret', 'wp-vimeo-videos' ) . '</strong>';
						$txt_a_tok = '<strong>' . __( 'Access Token', 'wp-vimeo-videos' ) . '</strong>';
						echo sprintf( __( 'After your upload access is approved you will need to create access token and also collect the required credentials such as %s, %s, %s.', 'wp-vimeo-videos' ), $txt_c_id, $txt_c_sec, $txt_a_tok );
						?>
					</p>
				</div>
			</div>
			<div class="dgv-instructions-col4">
				<div class="dgv-instructions-instruction">
					<h4 class="navy"><?php _e( '4. Setup Credentials', 'wp-vimeo-videos' ); ?></h4>
					<p>
						<?php
						$txt_settings = '<a href="' . admin_url( 'options-general.php?page=' . WP_DGV_Admin::PAGE_SETTINGS ) . '">' . __( 'settings', 'wp-vimeo-videos' ) . '</a>';
						echo sprintf( __( 'Finally, assuming that you have upload access and all the credentials from the step 3, you need to enter those in the plugin %s page', 'wp-vimeo-videos' ), $txt_settings );
						?>
					</p>
				</div>
			</div>
		</div>
		<div class="dgv-instructions-row">
			<div class="dgv-instructions-colf wvv-pt-0 wvv-pb-0">
				<a class="button-small button-primary" target="_blank" href="<?php echo esc_url($url_guide); ?>"><?php _e( 'Read guide', 'wp-vimeo-videos' ); ?></a>
			</div>
			<div class="dgv-instructions-colf wvv-pb-0">
				<hr/>
			</div>
		</div>
		<div class="dgv-instructions-row dgv-instructions-mb-10">
			<div class="dgv-instructions-colf">
				<div class="dgv-instructions-extra">
					<h4 class="navy"><?php _e( 'Premium Version', 'wp-vimeo-videos' ); ?> :)</h4>
					<p>
						<?php _e( 'We have premium version of this plugin that is improved, has more features, regular updates and dedicated support team ready to help you. If you are interested to find out about the complete list of the features click on the button below.', 'wp-vimeo-videos' ); ?>
					</p>
					<p>
						<a target="_blank" href="<?php echo esc_url(wvv_get_purchase_url()); ?>" class="button-small button-primary"><?php _e('Read more', 'wp-vimeo-videos'); ?></a>
					</p>
				</div>
			</div>
		</div>
        <a href="<?php echo esc_url( $dismiss_link ); ?>" class="notice-dismiss dgv-notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice', 'wp-vimeo-videos' ); ?>.</span></a>
    </div>
</div>
