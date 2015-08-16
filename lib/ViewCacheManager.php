<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Views;

use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;
use ICanBoogie\Storage\Storage;

use Icybee\Modules\Cache\CacheManager;
use Icybee\Modules\Cache\Module as CacheModule;

class ViewCacheManager implements CacheManager
{
	public $title = "Vues";
	public $description = "Index des vues des modules.";
	public $group = 'system';
	public $state = false;
	public $config_preview;

	/**
	 * @var Storage
	 */
	private $vars;

	public function __construct()
	{
		$app = \ICanBoogie\app();
		$this->vars = $app->vars;
		$this->state = $app->config['cache views'];
	}

	/**
	 * Clears the cache.
	 */
	public function clear()
	{
		unset($this->vars['cached_views']);

		return true;
	}

	/**
	 * Disables caching.
	 *
	 * Unsets the `enable_modules_cache` var.
	 */
	public function disable()
	{
		unset($this->vars['enable_views_cache']);

		return true;
	}

	/**
	 * Enables caching.
	 *
	 * Sets the `enable_modules_cache` var.
	 */
	public function enable()
	{
		$this->vars['enable_views_cache'] = true;

		return true;
	}

	/**
	 * Return stats about the cache.
	 */
	public function stat()
	{
		return CacheModule::get_vars_stat('#^cached_views$#');
	}

	/**
	 * Revokes the cache.
	 */
	static public function revoke()
	{
		Request::from(Operation::encode('cache/icybee.views/clear'))->post();
	}
}
