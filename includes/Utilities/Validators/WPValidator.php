<?php

namespace Vimeify\Core\Utilities\Validators;

class WPValidator {

	/**
	 * Check if gutenberg is active.
	 * @return bool
	 */
	public function is_gutenberg_active() {
		if ( function_exists( '\is_gutenberg_page' ) && \is_gutenberg_page() ) {
			// The Gutenberg plugin is on.
			return true;
		}

		require_once( ABSPATH . 'wp-admin/includes/screen.php' );

		$current_screen = get_current_screen();
		if ( ! is_null( $current_screen ) && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
			// Gutenberg page on 5+.
			return true;
		}

		return false;
	}

}