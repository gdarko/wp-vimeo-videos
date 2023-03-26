<?php

namespace Vimeify\Core\Abstracts;

use Vimeify\Core\Abstracts\Interfaces\PluginInterface;
use Vimeify\Core\Abstracts\Interfaces\ProviderInterface;
use Vimeify\Core\Plugin;

abstract class BaseProvider implements ProviderInterface {

	/**
	 * @var PluginInterface|Plugin
	 */
	protected $plugin;

	/**
	 * The plugin instance
	 *
	 * @param  PluginInterface  $plugin
	 */
	public function __construct( PluginInterface &$plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Registers sepcific piece of functionality
	 * @return void
	 */
	abstract public function register();

	/**
	 * Boot a plugin
	 *
	 * @param $provider_class
	 *
	 * @return ProviderInterface
	 */
	public function boot( $provider_class ) {
		$instance = new $provider_class( $this->plugin );
		$instance->register();

		return $instance;
	}
}