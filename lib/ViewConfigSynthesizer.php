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

use Icybee\Modules\Views\ViewOptions as Options;

/**
 * Synthesizes the `views` config.
 */
class ViewConfigSynthesizer
{
	/**
	 * Synthesizes the `views` config.
	 *
	 * @param array $fragments
	 *
	 * @return array
	 */
	public function __invoke(array $fragments)
	{
		$modules = $this->collect_modules($fragments);
		$this->resolve_assets($modules);
		$modules = $this->unwind_modules_collection($modules);
		$modules = $this->resolve_heritage($modules);

		return $this->unwind_views($modules);
	}

	/**
	 * Collects modules from config fragments.
	 *
	 * Because multiple module can be defined in a single fragment and a module identifier can
	 * be used several times in different fragments, modules are collected in an array which
	 * values have the following keys:
	 *
	 * - `id`: The module identifier.
	 * - `views`: The views defined for this module in a fragment.
	 * - `pathname`: The pathname of the fragment defining this module.
	 *
	 * @param array $fragments
	 *
	 * @return array
	 */
	protected function collect_modules(array $fragments)
	{
		$modules = [];

		foreach ($fragments as $pathname => $fragment)
		{
			foreach ($fragment as $module_id => $module)
			{
				$modules[] = [

					'id' => $module_id,
					'views' => $module,
					'pathname' => $pathname

				];
			}
		}

		return $modules;
	}

	/**
	 * Resolves assets.
	 *
	 * @param array $modules
	 */
	protected function resolve_assets(array &$modules)
	{
		foreach ($modules as &$module)
		{
			$assets = $this->collect_assets($module);

			if (!$assets)
			{
				continue;
			}

			$pathname = $module['pathname'];
			$path = dirname($pathname) . DIRECTORY_SEPARATOR;

			foreach ($assets as &$relative_path)
			{
				$absolute_path = realpath($path . $relative_path);

				if (!$absolute_path)
				{
					throw new \LogicException("Unable to resolve path `$relative_path` in `$pathname` ({$module['id']})");
				}

				$relative_path = $absolute_path;
			}
		}
	}

	/**
	 * Collect a module's assets by reference.
	 *
	 * @param array $module
	 *
	 * @return array
	 */
	protected function collect_assets(array &$module)
	{
		$assets = [];

		foreach ($module['views'] as &$view)
		{
			if (empty($view[Options::ASSETS]))
			{
				continue;
			}

			foreach ($view[Options::ASSETS] as &$asset)
			{
				$assets[] = &$asset;
			}
		}

		return $assets;
	}

	/**
	 * Unwind modules into view.
	 *
	 * @param array $modules
	 *
	 * @return array
	 */
	protected function unwind_modules_collection(array $modules)
	{
		$views = [];

		foreach ($modules as $module)
		{
			$views[$module['id']][] = $module['views'];
		}

		return array_map(function($v) {

			return call_user_func_array('array_merge', $v);

		}, $views);
	}

	/**
	 * Resolves heritage between modules.
	 *
	 * @param array $modules
	 *
	 * @return array
	 */
	protected function resolve_heritage(array $modules)
	{
		foreach ($modules as &$module)
		{
			if (empty($module[Options::DIRECTIVE_INHERITS]))
			{
				continue;
			}

			$inherits = $module[Options::DIRECTIVE_INHERITS];
			unset($module[Options::DIRECTIVE_INHERITS]);

			$module = \ICanBoogie\array_merge_recursive($modules[$inherits], $module);
		}

		return $modules;
	}

	/**
	 * Unwind modules into views.
	 *
	 * @param array $modules
	 *
	 * @return array
	 */
	protected function unwind_views(array $modules)
	{
		$views = [];

		foreach ($modules as $module_id => $module)
		{
			foreach ($module as $view_id => $options)
			{
				$views["$module_id/$view_id"] = Options::normalize($options + [

					Options::MODULE => $module_id,
					Options::TYPE => $view_id

				]);
			}
		}

		return $views;
	}
}
