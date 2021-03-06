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

use ICanBoogie\I18n;
use ICanBoogie\Module\Descriptor;

use Brickrouge\Document;
use Brickrouge\Element;

/**
 * View editor element.
 */
class ViewEditorElement extends Element implements \Icybee\Modules\Editor\EditorElement
{
	static protected function add_assets(Document $document)
	{
		parent::add_assets($document);

		$document->css->add('ViewEditorElement.css');
		$document->js->add('ViewEditorElement.js');
	}

	public function __construct(array $attributes)
	{
		parent::__construct('div', $attributes + [

			'class' => 'view-editor'

		]);
	}

	public function render_inner_html()
	{
		$app = $this->app;
		$selected_category = null;
		$selected_subcategory = null;
		$selected_view = $this['value'];

		$categories = [];
		$descriptors = $app->modules->descriptors;
		$views = $app->views;

		foreach ($views as $id => $view)
		{
			list($module_id, $type) = explode('/', $id) + [ 1 => null ];

			if (!isset($app->modules[$module_id]))
			{
				continue;
			}

			$category = 'Misc';
			$subcategory = 'Misc';

			if ($type !== null && isset($descriptors[$module_id]))
			{
				$descriptor = $descriptors[$module_id];

				if (isset($descriptor[Descriptor::CATEGORY]))
				{
					$category = $descriptors[$module_id][Descriptor::CATEGORY];
					$category = $this->t($category, [ ], [ 'scope' => 'module_category' ]);
				}

				$subcategory = $descriptor[Descriptor::TITLE];
				$subcategory = $this->t(strtr($module_id, '.', '_'), [ ], [ 'scope' => 'module_title', 'default' => $subcategory ]);
			}

			$categories[$category][$subcategory][$id] = $view;

			if ($id == $selected_view)
			{
				$selected_category = $category;
				$selected_subcategory = $subcategory;
			}
		}

		uksort($categories, 'ICanBoogie\unaccent_compare_ci');

		foreach ($categories as $category => $subcategories)
		{
			uksort($subcategories, 'ICanBoogie\unaccent_compare_ci');

			$categories[$category] = $subcategories;
		}

		$rendered_categories = $this->render_categories($categories, $selected_category);
		$rendered_subcategories = $this->render_subcategories($categories, $selected_category, $selected_subcategory);
		$rendered_views = $this->render_views($categories, $selected_category, $selected_subcategory, $selected_view);

		return parent::render_inner_html() . <<<EOT
<table>
	<tr>
		<td class="view-editor-categories">$rendered_categories</td>
		<td class="view-editor-subcategories">$rendered_subcategories</td>
		<td class="view-editor-views">$rendered_views</td>
	</tr>
</table>
EOT;
	}

	protected function render_categories(array $categories, $selected)
	{
		$html = '';

		foreach ($categories as $category => $dummy)
		{
			$html .= '<li' . ($category == $selected ? ' class="active selected"' : '') . '><a href="#select">' . \ICanBoogie\escape($category) . '</a></li>';
		}

		return '<ul>' . $html . '</ul>';
	}

	protected function render_subcategories(array $categories, $selected_category, $selected_subcategory)
	{
		$html = '';

		foreach ($categories as $category => $subcategories)
		{
			$html .= '<ul' . ($category == $selected_category ? ' class="active selected"' : '') . '>';

			foreach ($subcategories as $subcategory => $views)
			{
				$html .= '<li' . ($subcategory == $selected_subcategory ? ' class="active selected"' : '') . '><a href="#select">' . \ICanBoogie\escape($subcategory) . '</a></li>';
			}

			$html .= '</ul>';
		}

		return $html;
	}

	protected function render_views(array $categories, $selected_category, $selected_subcategory, $selected_view)
	{
		$html = '';
		$context = $this->app->site->path;
		$name = $this['name'];

		foreach ($categories as $category => $subcategories)
		{
			foreach ($subcategories as $subcategory => $views)
			{
				$active = '';
				$items = array();

				foreach ($views as $id => $view)
				{
					if (!$view[ViewOptions::TITLE])
					{
						continue;
					}

					$title = $this->t($view[ViewOptions::TITLE], $view[ViewOptions::TITLE_ARGS]);

					$description = null;

					if (isset($view['description']))
					{
						$description = $view['description'];

						// FIXME-20101008: finish that ! it this usefull anyway ?

						$description = strtr($description, [

							'#{url}' => $context . '/admin/'

						]);
					}

					if ($id == $selected_view)
					{
						$active = ' class="active"';
					}

					$items[] = new Element(Element::TYPE_RADIO, [

						Element::LABEL => $title,
						Element::DESCRIPTION => $description,

						'name' => $name,
						'value' => $id,
						'checked' => ($id == $selected_view)

					]);
				}

				$html .= "<ul$active><li>" . implode('</li><li>', $items) . '</li></ul>';
			}
		}

		return $html;
	}
}
