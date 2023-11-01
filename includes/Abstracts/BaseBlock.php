<?php

namespace Vimeify\Core\Abstracts;

use Vimeify\Core\Abstracts\Interfaces\PluginInterface;
use Vimeify\Core\Abstracts\Interfaces\ViewInterface;
use Vimeify\Core\Plugin;

abstract class BaseBlock implements ViewInterface {

	/**
	 * The plugin interface
	 * @var PluginInterface|Plugin
	 */
	protected $plugin = null;
	/**
	 * The args
	 * @var array|mixed
	 */
	protected $args = [];

	/**
	 * Constructor
	 *
	 * @param PluginInterface $plugin
	 * @param $args
	 */
	public function __construct( $plugin, $args = [] ) {
		if ( ! empty( $args ) ) {
			$this->args = wp_parse_args( $args, [] );
		}
		$this->plugin = $plugin;
	}

	/**
	 * Registers block editor assets
	 * @return void
	 */
	abstract public function register_block();

	/**
	 * Registers block editor assets
	 * @return void
	 */
	abstract public function register_block_editor_assets();

	/**
	 * Dynamic render for the upload block
	 *
	 * @param $block_attributes
	 * @param $content
	 *
	 * @return string
	 */
	abstract public function render_block( $block_attributes, $content );

}