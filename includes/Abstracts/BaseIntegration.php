<?php

namespace Vimeify\Core\Abstracts;

use Vimeify\Core\Abstracts\Interfaces\IntegrationInterface;

abstract class BaseIntegration extends BaseProvider implements IntegrationInterface {

	/**
	 * Is create or edit screen?
	 *
	 * @param $post_type
	 *
	 * @return bool
	 */
	protected function is_editor( $post_type ) {

		$post_type = (array) $post_type;

		if ( ! is_admin() ) {
			return false;
		}

		$is_create = isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], $post_type );
		if ( $is_create ) {
			return true;
		}
		global $post;
		if ( isset( $post->ID ) && in_array( $post->post_type, $post_type ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
			return true;
		}

		return false;
	}

	/**
	 * The file path
	 * @param $path
	 *
	 * @return string
	 */
	protected function file( $path = '' ) {
		$fullpath = $this->assemble_path( $this->plugin->path(), $path );
		return str_replace('/', DIRECTORY_SEPARATOR, $fullpath);
	}

	/**
	 * The file url
	 * @param $path
	 *
	 * @return string
	 */
	protected function url( $path = '' ) {
		return $this->assemble_path( $this->plugin->url(), $path );
	}

	/**
	 * Assembles path
	 * @param $root
	 * @param $path
	 *
	 * @return string
	 */
	protected function assemble_path( $root, $path = '' ) {
		$name    = explode( '\\', get_class( $this ) );
		$dirname = $name[ count( $name ) - 1 ];
		return sprintf( '%sincludes/Integrations/%s/%s', $root, $dirname, $path );
	}

    /**
     * Activates the integration
     * @return bool
     */
    public function activate()
    {
        if(!$this->can_activate()) {
            return false;
        }

        $this->register();
        return true;
    }

}