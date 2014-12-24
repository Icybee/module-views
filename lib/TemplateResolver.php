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

use ICanBoogie\Module\Descriptor;

/**
 * @property-read array $templates Possible templates.
 */
class TemplateResolver extends \ICanBoogie\Object
{
	/**
	 * View identifier.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Views type, one of "home", "list", "view"...
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Identifier of the module for which a view is created.
	 *
	 * @var string
	 */
	protected $module_id;

	public function __construct($id, $type, $module_id)
	{
		$this->id = $id;
		$this->type = $type;
		$this->module_id = $module_id;
	}

	protected function lazy_get_templates()
	{
		$id = $this->id;
		$templates = [];

		$templates_base = [];

		$parts = explode('/', $id);
		$module_id = array_shift($parts);
		$type = array_pop($parts);

		while (count($parts))
		{
			$templates_base[] = implode('--', $parts) . '--' . $type;

			array_pop($parts);
		}

		$templates_base[] = $type;

		$templates_base = array_unique($templates_base);

		$descriptors = $this->app->modules->descriptors;
		$descriptor = $descriptors[$this->module_id];

		$autoconfig = \ICanBoogie\get_autoconfig();
		$app_paths = array_reverse(\IcanBoogie\resolve_app_paths($autoconfig['root'] . DIRECTORY_SEPARATOR . 'protected'));

		while ($descriptor)
		{
			foreach ($templates_base as $template)
			{
				foreach ($app_paths as $path)
				{
					$pathname = $path . 'templates/views/' . \ICanBoogie\normalize($descriptor[Descriptor::ID]) . '--' . $template;
					$templates[] = $pathname;
				}

				$pathname = $descriptor[Descriptor::PATH] . 'templates/' . $template;
				$templates[] = $pathname;

				$pathname = $descriptor[Descriptor::PATH] . 'views/' . $template;
				$templates[] = $pathname;
			}

			$descriptor = $descriptor[Descriptor::INHERITS] ? $descriptors[$descriptor[Descriptor::INHERITS]] : null;
		}

		foreach ($templates_base as $template)
		{
			foreach ($app_paths as $path)
			{
				$pathname = $path . 'templates/views/' . $template;
				$templates[] = $pathname;
			}
		}

		return $templates;
	}

	public function __invoke()
	{
		$templates = $this->templates;

		$handled = [ 'php', 'html' ];

		foreach ($templates as $template)
		{
			foreach ($handled as $extension)
			{
				$pathname = $template . '.' . $extension;

				if (file_exists($pathname))
				{
					return $pathname;
				}
			}
		}
	}
}
