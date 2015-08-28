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
		$directives = $this->extract_directives($modules);
		$this->process_directives($directives, $modules);

		return $this->unwind_modules($modules);
	}

	/**
	 * Collects modules from config fragments.
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
			$path = dirname($pathname) . DIRECTORY_SEPARATOR;

			foreach ($fragment as $module_id => $module)
			{
				$modules[$module_id] = [ Options::DIRECTIVE_PATH => $path ] + $module;
			}
		}

		return $modules;
	}

	/**
	 * Extracts directives from modules.
	 *
	 * **Note:** The directives are removed from the definitions.
	 *
	 * @param array $modules
	 *
	 * @return array
	 *
	 * @throws \Exception if a directive is invalid.
	 */
	protected function extract_directives(array &$modules)
	{
		$directives = [];

		foreach ($modules as $module_id => &$module)
		{
			foreach ($module as $key => $value)
			{
				if ($key{0} !== Options::DIRECTIVE_PREFIX)
				{
					continue;
				}

				$directive = substr($key, 1);

				$this->assert_directive_is_valid($directive);

				unset($module[$key]);

				$directives[$directive][$module_id] = $value;
			}
		}

		return $directives;
	}

	/**
	 * Asserts that a directive is valid.
	 *
	 * @param string $directive
	 *
	 * @throws \Exception if the specified directive is invalid
	 */
	protected function assert_directive_is_valid($directive)
	{
		if (!in_array(Options::DIRECTIVE_PREFIX . $directive, [ Options::DIRECTIVE_INHERITS, Options::DIRECTIVE_PATH ]))
		{
			throw new \Exception("Invalid directive [$directive]");
		}
	}

	/**
	 * Processes directives, altering modules.
	 *
	 * @param array $directives
	 * @param array $modules
	 */
	protected function process_directives(array $directives, array &$modules)
	{
		foreach ($directives as $directive => $values)
		{
			$method = 'process_directive_' . $directive;

			foreach ($values as $module_id => $value)
			{
				$this->$method($value, $module_id, $modules);
			}
		}
	}

	/**
	 * Processes the directive {@link Options::DIRECTIVE_PATH}.
	 *
	 * Assets are resolved relatively to the value of the directive.
	 *
	 * @param string $directive_value
	 * @param string $module_id
	 * @param array $modules
	 */
	protected function process_directive_path($directive_value, $module_id, array &$modules)
	{
		foreach ($modules[$module_id] as $view_id => &$options)
		{
			if (empty($options[Options::ASSETS]))
			{
				continue;
			}

			foreach ($options[Options::ASSETS] as &$relative_path)
			{
				$absolute_path = realpath($directive_value . $relative_path);

				if (!$absolute_path)
				{
					throw new \LogicException("Unable to resolve $relative_path from $directive_value.");
				}

				$relative_path = $absolute_path;
			}
		}
	}

	/**
	 * Processes the directive {@link Options::DIRECTIVE_INHERITS}.
	 *
	 * View definitions are inherited from another module.
	 *
	 * @param string $directive_value
	 * @param string $module_id
	 * @param array $modules
	 */
	protected function process_directive_inherits($directive_value, $module_id, array &$modules)
	{
		$modules[$module_id] = \ICanBoogie\array_merge_recursive($modules[$directive_value], $modules[$module_id]);
	}

	/**
	 * Unwind modules into views.
	 *
	 * @param array $modules
	 *
	 * @return array
	 */
	protected function unwind_modules(array $modules)
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
