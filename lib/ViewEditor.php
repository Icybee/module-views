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

/**
 * View editor.
 */
class ViewEditor implements \Icybee\Modules\Editor\Editor
{
	private $app;

	public function __construct()
	{
		$this->app = \ICanBoogie\app();
	}

	/**
	 * Returns the content as is.
	 */
	public function serialize($content)
	{
		return $content;
	}

	/**
	 * Returns the serialized content as is.
	 */
	public function unserialize($serialized_content)
	{
		return $serialized_content;
	}

	/**
	 * @return ViewEditorElement
	 */
	public function from(array $attributes)
	{
		return new ViewEditorElement($attributes);
	}

	public function render($id, $engine=null, $template=null)
	{
		$definition = $this->app->views[$id];

		$patron = \Patron\Engine::get_singleton();
		$page = $this->resolve_view_page();
		$class = $this->resolve_view_classname($definition);

		$view = new $class($id, $definition, $patron, $this->app->document, $page);
		$rc = $view();

		return $template ? $engine($template, $rc) : $rc;
	}

	/**
	 * Resolves the page on which the view is displayed.
	 *
	 * @return \Icybee\Modules\Pages\Page
	 */
	private function resolve_view_page()
	{
		$request = $this->app->request;

		if (isset($request->context->page))
		{
			return $request->context->page;
		}

		$site = $this->app->site;
		$page = $site->resolve_view_target($id);

		if ($page)
		{
			return $page;
		}

		return $site->home;
	}

	/**
	 * Resolves the name of the class that should be used to instantiate the view.
	 *
	 * If `module` is specified in the view definition, the name is resolved according to the
	 * module's hierarchy.
	 *
	 * @param array $definition
	 *
	 * @return string The class that should be used to instantiate the view.
	 */
	private function resolve_view_classname(array $definition)
	{
		$app = $this->app;
		$classname = empty($definition[ViewOptions::CLASSNAME]) ? null : $definition[ViewOptions::CLASSNAME];

		if (!empty($definition[ViewOptions::MODULE]))
		{
			$resolved_classname = $app->modules->resolve_classname('View', $definition[ViewOptions::MODULE]);

			if (!$classname)
			{
				$classname = $resolved_classname;
			}
			else if ($classname === $resolved_classname)
			{
				$app->logger->debug(\ICanBoogie\format("The view class %class can be resolved from the module, it is recommended to avoid its definition: :definition", [

					'class' => $classname,
					'definition' => $definition

				]));
			}
		}

		return $classname ?: 'Icybee\Modules\Views\View';
	}
}
