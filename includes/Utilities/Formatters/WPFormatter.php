<?php

namespace Vimeify\Core\Utilities\Formatters;

class WPFormatter {

	/**
	 * Returns the user edit url
	 *
	 * @param  int  $id
	 *
	 * @return string
	 *
	 * @since 1.7.3
	 */
	public function get_user_edit_url( $id ) {
		if ( is_object( $id ) && isset( $id->ID ) ) {
			$id = $id->ID;
		} elseif ( is_array( $id ) && isset( $id['ID'] ) ) {
			$id = $id['ID'];
		}

		return admin_url( sprintf( 'user-edit.php?user_id=%s', $id ) );
	}

	/**
	 * Returns link to the user profile
	 *
	 * @param $id
	 *
	 * @return string|void
	 *
	 * @since 1.7.3
	 */
	public function get_user_edit_link( $id ) {

		$user = wp_cache_get( 'user_' . $id, 'dgv' );
		if ( false === $user ) {
			$user = get_user_by( 'id', $id );
			wp_cache_set( 'user_' . $id, $user, 'dgv' );
		}

		$name = '';
		$link = '';
		if ( is_a( $user, '\WP_User' ) ) {
			$link = $this->get_user_edit_url( $user->ID );
			if ( ! empty( $user->display_name ) ) {
				$name = $user->display_name;
			} elseif ( ! empty( $user->user_nicename ) ) {
				$name = $user->user_nicename;
			} elseif ( ! empty( $user->user_login ) ) {
				$name = $user->user_login;
			}
		}

		return $name ? sprintf( '<a href="%s">%s</a>', $link, $name ) : __( 'Unknown' );
	}


}