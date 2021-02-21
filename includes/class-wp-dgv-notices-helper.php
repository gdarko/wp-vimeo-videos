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

/**
 * Class WP_DGV_Notices_Helper
 *
 * Responsible for displaying notices in the admin dashboard
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class WP_DGV_Notices_Helper {

	/**
	 * The plugin name
	 * @var string
	 */
	private $title = 'WP Vimeo Videos';

	/**
	 * The notices prefix
	 * @var string
	 */
	private $prefix = 'wvv_';

	/**
     * Dismiss expiry
	 * @var int
	 */
	private $expiry = 8640000;

	/**
	 * @var WP_DGV_Notices_Helper
	 */
	private static $_instance;

	/**
     * The queue of notices
	 * @var stdClass
	 */
	private $admin_notices;

	/**
	 * Notice types
	 */
	const TYPES = 'error,warning,info,success';

	/**
	 * MGO_Admin_Notices constructor.
	 */
	private function __construct() {
		$this->admin_notices = new stdClass();
		foreach ( explode( ',', self::TYPES ) as $type ) {
			$this->admin_notices->{$type} = array();
		}
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( 'admin_footer', array( &$this, 'admin_footer' ), PHP_INT_MAX - 1000 );
	}

	/**
	 * Return the current instance
	 * @return WP_DGV_Notices_Helper
	 */
	public static function instance() {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Admin init
	 */
	public function admin_init() {
		$notice_id      = filter_input( INPUT_GET, $this->prefix . 'notice_id', FILTER_SANITIZE_STRING );
		$dismiss_option = filter_input( INPUT_GET, $this->prefix . 'dismiss', FILTER_SANITIZE_STRING );
		if ( ! empty( $notice_id ) && is_string( $dismiss_option ) ) {
			set_transient( $this->get_notice_key( $notice_id ), 'd', $this->expiry );
			wp_die();
		}
	}

	/**
	 * Enqueue the admin scripts
	 */
	public function admin_footer() {
		?>
        <script>
            /**
             * Admin code for dismissing notifications.
             *
             */
            (function ($) {
                'use strict';
                $(function () {
                    $('.<?php echo $this->prefix; ?>notice').on('click', '.notice-dismiss', function (event, el) {
                        var $notice = $(this).parent('.notice.is-dismissible');
                        var dismiss_url = $notice.attr('data-dismiss-url');
                        if (dismiss_url) {
                            $.get(dismiss_url);
                        }
                    });
                });
            })(jQuery);
        </script>
		<?php
	}

	/**
	 * Show admin notices
	 */
	public function admin_notices() {
		foreach ( explode( ',', self::TYPES ) as $type ) {
			foreach ( $this->admin_notices->{$type} as $admin_notice ) {
				$dismiss_url = add_query_arg( array(
					$this->prefix . 'dismiss'   => $admin_notice->dismiss_option,
					$this->prefix . 'notice_id' => $admin_notice->id,
				), admin_url() );

				$value = get_transient( $this->get_notice_key( $admin_notice->id ) );

				if ( ! $value || $value !== 'd' ) {
					?>
                    <div class="notice <?php echo $this->prefix; ?>notice notice-<?php echo $type;
					if ( $admin_notice->dismiss_option ) {
						echo ' is-dismissible" data-dismiss-url="' . esc_url( $dismiss_url );
					} ?>">
                        <h2><?php echo "$this->title $type"; ?></h2>
                        <p><?php echo $admin_notice->message; ?></p>
                    </div>
					<?php
				}
			}
		}
	}

	/**
	 * Add error notification message.
	 *
	 * @param $id
	 * @param $message
	 * @param $dismiss_option
	 */
	public function error( $id, $message, $dismiss_option = false ) {
		$this->notice( 'error', $id, $message, $dismiss_option );
	}

	/**
	 * Add warning notification message.
	 *
	 * @param $id
	 * @param $message
	 * @param $dismiss_option
	 */
	public function warning( $id, $message, $dismiss_option = false ) {
		$this->notice( 'warning', $id, $message, $dismiss_option );
	}

	/**
	 * Add success notification message.
	 *
	 * @param $id
	 * @param $message
	 * @param $dismiss_option
	 */
	public function success( $id, $message, $dismiss_option = false ) {
		$this->notice( 'success', $id, $message, $dismiss_option );
	}

	/**
	 * Add info notification message.
	 *
	 * @param $id
	 * @param $message
	 * @param $dismiss_option
	 */
	public function info( $id, $message, $dismiss_option = false ) {
		$this->notice( 'info', $id, $message, $dismiss_option );
	}

	/**
	 * Add notice
	 *
	 * @param $type
	 * @param $id
	 * @param $message
	 * @param $dismiss_option
	 */
	private function notice( $type, $id, $message, $dismiss_option ) {
		$notice                 = new stdClass();
		$notice->id             = $id;
		$notice->message        = $message;
		$notice->dismiss_option = $dismiss_option;

		$this->admin_notices->{$type}[] = $notice;
	}

	/**
	 * Returns the notice database key
	 *
	 * @param $id
	 *
	 * @return string
	 */
	private function get_notice_key( $id ) {
		return $this->prefix . 'dismissed_' . $id;
	}
}
WP_DGV_Notices_Helper::instance();