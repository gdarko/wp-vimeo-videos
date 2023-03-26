<?php

namespace Vimeify\Core\Abstracts\Interfaces;

use Vimeify\Core\Abstracts\BaseProvider;

interface PluginInterface {

	/**
	 * The plugin interface constructor
	 *
	 * @param  SystemInterface  $system
	 */
	public function __construct( SystemInterface $system );

	/**
	 * The system
	 * @return SystemInterface
	 */
	public function system();

	/**
	 * Returns the id of the plugin
	 * @return mixed|null
	 */
	public function id();

	/**
	 * Returns the name of the plugin
	 * @return mixed|null
	 */
	public function name();

	/**
	 * Returns the slug of the plugin
	 * @return mixed|null
	 */
	public function slug();

	/**
	 * Returns the icon of the plugin
	 * @return array|null
	 */
	public function icon();

	/**
	 * Returns the main file of the plugin
	 * @return mixed|null
	 */
	public function file();

	/**
	 * Returns the basename of the plugin
	 * @return mixed|null
	 */
	public function basename();

	/**
	 * Returns the path of the plugin
	 * @return mixed|null
	 */
	public function path();

	/**
	 * Returns the url of the plugin
	 * @return mixed
	 */
	public function url();

	/**
	 * Returns the plugin version
	 * @return mixed|null
	 */
	public function plugin_version();

	/**
	 * Returns the database version
	 * @return mixed|null
	 */
	public function database_version();

	/**
	 * Returns the minimum PHP version for this plugin
	 * @return mixed|null
	 */
	public function minimum_php_version();

	/**
	 * Returns the minimum WP version for this plugin
	 * @return mixed|null
	 */
	public function minimum_wp_version();

	/**
	 * Returns the settings key
	 * @return mixed
	 */
	public function settings_key();

	/**
	 * Returns the documentation url
	 * @return mixed
	 */
	public function documentation_url();

	/**
	 * Returns the commercial url
	 * @return mixed
	 */
	public function commercial_url();

	/**
	 * Returns the settings url
	 * @return mixed
	 */
	public function settings_url();

	/**
	 * Add Integration
	 *
	 * @param  ProviderInterface  $provider
	 *
	 * @return void
	 */
	public function add_integration(ProviderInterface $provider);

	/**
	 * List of integrations
	 * @return BaseProvider[]
	 */
	public function get_integrations();
}