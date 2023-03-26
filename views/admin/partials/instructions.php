<?php

/* @var \Vimeify\Core\Plugin $plugin */

// Urls
$url_guide    = $plugin->documentation_url();
$url_purchase = $plugin->commercial_url();

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
                <p class="lead"><?Php _e( 'Thanks for installing', 'wp-vimeo-videos-pro' ); ?> <strong
                            class="green"><?php _e( 'Video Uploads for Vimeo', 'wp-vimeo-videos-pro' ); ?></strong></p>
                <p class="desc"><?php echo sprintf( __( 'This plugin allows you to easily upload and embed Vimeo videos through your WordPress website.', 'wp-vimeo-videos-pro' ) ); ?></p>
                <p class="desc"><?php echo sprintf( __( 'To %s please follow the steps below:', 'wp-vimeo-videos-pro' ), '<strong>' . __( 'get started', 'wp-vimeo-videos-pro' ) . '</strong>' ); ?></p>
            </div>
        </div>
        <div class="dgv-instructions-row">
            <div class="dgv-instructions-col4">
                <div class="dgv-instructions-instruction">
                    <h4 class="navy"><?php _e( '1. Vimeo Developer Portal', 'wp-vimeo-videos-pro' ); ?></h4>
                    <p>
						<?php
						$txt_create_app = '<strong>' . __( 'Create App', 'wp-vimeo-videos-pro' ) . '</strong>';
						$txt_dev_portal = '<a target="_blank" href="https://developer.vimeo.com/">' . __( 'Vimeo Developer Portal', 'wp-vimeo-videos-pro' ) . '</a>';
						echo sprintf( __( 'To get started and successfully connect the plugin to Vimeo you will need to sign up at %s and then %s that will be used by your website.', 'wp-vimeo-videos-pro' ), $txt_dev_portal, $txt_create_app );
						?>
                    </p>
                </div>
            </div>
            <div class="dgv-instructions-col4">
                <div class="dgv-instructions-instruction">
                    <h4 class="navy"><?php _e( '2. Request Upload Access', 'wp-vimeo-videos-pro' ); ?></h4>
                    <p>
						<?php _e( 'In order to be able to upload videos from external software like this plugin you need to request Upload Access from Vimeo and wait for approval.', 'wp-vimeo-videos-pro' ); ?><br/>
                        <strong><?php _e( '(1-5 days required for approval)', 'wp-vimeo-videos-pro' ); ?></strong>
                    </p>
                </div>
            </div>
            <div class="dgv-instructions-col4">
                <div class="dgv-instructions-instruction">
                    <h4 class="navy"><?php _e( '3. Obtain API Credentials', 'wp-vimeo-videos-pro' ); ?></h4>
                    <p>
						<?php
						$txt_c_id  = '<strong>' . __( 'Client ID', 'wp-vimeo-videos-pro' ) . '</strong>';
						$txt_c_sec = '<strong>' . __( 'Client Secret', 'wp-vimeo-videos-pro' ) . '</strong>';
						$txt_a_tok = '<strong>' . __( 'Access Token', 'wp-vimeo-videos-pro' ) . '</strong>';
						echo sprintf( __( 'After your upload access is approved you will need to create access token and also collect the required credentials such as %s, %s, %s.', 'wp-vimeo-videos-pro' ), $txt_c_id, $txt_c_sec, $txt_a_tok );
						?>
                    </p>
                </div>
            </div>
            <div class="dgv-instructions-col4">
                <div class="dgv-instructions-instruction">
                    <h4 class="navy"><?php _e( '4. Setup Credentials', 'wp-vimeo-videos-pro' ); ?></h4>
                    <p>
						<?php
						$txt_settings = '<a href="' . $plugin->settings_url() . '">' . __( 'settings', 'wp-vimeo-videos-pro' ) . '</a>';
						echo sprintf( __( 'Finally, assuming that you have upload access and all the credentials from the step 3, you need to enter those in the plugin %s page', 'wp-vimeo-videos-pro' ), $txt_settings );
						?>
                    </p>
                </div>
            </div>
        </div>
        <div class="dgv-instructions-row">
            <div class="dgv-instructions-colf wvv-pt-0 wvv-pb-0">
               <a class="button-small button-primary" target="_blank" href="<?php echo esc_url($url_guide); ?>"><?php _e( 'Read guide', 'wp-vimeo-videos-pro' ); ?></a>
            </div>
            <div class="dgv-instructions-colf wvv-pb-0">
                <hr/>
            </div>
        </div>
        <div class="dgv-instructions-row dgv-instructions-mb-10">
            <div class="dgv-instructions-colf">
                <div class="dgv-instructions-extra">
                    <h4 class="navy"><?php _e( 'Support', 'wp-vimeo-videos-pro' ); ?></h4>
                    <p>
						<?php _e( 'If you need any help setting up the plugin, feel free to get in touch with us and we can discuss more!', 'wp-vimeo-videos-pro' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <a href="<?php echo esc_url($dismiss_link); ?>" class="notice-dismiss dgv-notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice', 'wp-vimeo-videos-pro' ); ?>.</span></a>
    </div>
</div>