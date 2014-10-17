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

use ICanBoogie\ActiveRecord\FetcherInterface;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\AuthenticationRequired;
use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\I18n;
use ICanBoogie\HTTP\HTTPError;
use ICanBoogie\Module;
use ICanBoogie\Object;

use Brickrouge\Document;
use Brickrouge\Element;
use Brickrouge\Pager;

use BlueTihi\Context;

use Icybee\Modules\Nodes\Node;
use Icybee\Modules\Views\View\RenderEvent;
use Icybee\Modules\Views\View\AlterRecordsEvent;
use Icybee\Modules\Views\View\BeforeAlterRecordsEvent;

/**
 * A view on provided data.
 *
 * @property-read string $id The identifier of the view.
 * @property-read mixed $data The data provided by the view's provider.
 * @property-read array $default_conditions Default conditions.
 * @property-read array $user_conditions User conditions, overwriting default conditions.
 * @property-read array $important_conditions Important conditions, overwriting user conditions.
 * @property-read array $conditions Conditions resolved from the _default_, _user_, and _important_ conditions.
 */
class View extends Object
{
	const ACCESS_CALLBACK = 'access_callback';
	const ASSETS = 'assets';
	const CLASSNAME = 'class';
	const CONDITIONS = 'conditions';
	const DEFAULT_CONDITIONS = 'default_conditions';
	const PROVIDER = 'provider';
	const RENDERS = 'renders';
	const RENDERS_ONE = 1;
	const RENDERS_MANY = 2;
	const RENDERS_OTHER = 3;
	const TITLE = 'title';

	protected $id;

	protected function get_id()
	{
		return $this->id;
	}

	/**
	 * The amount of data the view is rendering.
	 *
	 * - RENDERS_ONE: Renders a record.
	 *
	 * - RENDERS_MANY: Renders a collection of records. A 'range' value is added to the rendering
	 * context the following properties:
	 *     - (int) limit: The maximum number of record to render.
	 *     - (int) page: The starting page.
	 *     - (int) count: The total number of records. This value is to be entered by the provider.
	 *
	 * - RENDERS_OTHER: Renders an unknown amount of data.
	 *
	 * The property is read-only.
	 *
	 * @var int
	 */
	protected $renders;

	protected function get_renders()
	{
		return $this->renders;
	}

	protected $options;

	protected function get_options()
	{
		return $this->options;
	}

	protected $engine;
	protected $document;
	protected $page;
	protected $template;

	protected $module;

	protected function lazy_get_module()
	{
		global $core;

		if (isset($this->module))
		{
			return $this->module;
		}

		return $core->modules[$this->module_id];
	}

	private $data;

	protected function get_data()
	{
		return $this->data;
	}

	public $module_id;
	public $type;

	public function __construct($id, array $options, $engine, $document, $page, $template=null)
	{
		unset($this->module);

		$this->options = $options + [

			self::CONDITIONS => [],
			self::DEFAULT_CONDITIONS => []

		];

		$this->id = $id;
		$this->type = $options['type'];
		$this->module_id = $options['module'];
		$this->renders = $options['renders'];

		$this->engine = $engine;
		$this->document = $document;
		$this->page = $page;
		$this->template = $template;
	}

	/**
	 * Return the default conditions.
	 *
	 * @return array
	 */
	protected function get_default_conditions()
	{
		if ($this->renders == self::RENDERS_ONE)
		{
			$limit = 1;
		}
		else
		{
			$limit = $this->page->site->metas[$this->module->flat_id . '.limits.' . $this->type] ?: null;
		}

		return $this->options[self::DEFAULT_CONDITIONS] + [

			'page' => 0,
			'limit' => $limit

		];
	}

	/**
	 * Return user conditions.
	 *
	 * @return array
	 */
	protected function get_user_conditions()
	{
		// FIXME-20140706: User conditions should be provider to us
		return $_GET;
	}

	/**
	 * Return important conditions.
	 *
	 * @return array
	 */
	protected function get_important_conditions()
	{
		return $this->page->url_variables + $this->options[self::CONDITIONS];
	}

	/**
	 * Return the conditions resolved from the default conditions, user conditions and important
	 * conditions.
	 *
	 * User conditions overwrite default conditions, and important conditions overwrite user
	 * conditions.
	 *
	 * @return array
	 */
	protected function get_conditions()
	{
		return $this->important_conditions + $this->user_conditions + $this->default_conditions;
	}

	/**
	 * Filter conditions.
	 *
	 * Important conditions are removed and default conditions are removed as well if their value
	 * match.
	 *
	 * @param array $conditions
	 */
	protected function filter_conditions(array $conditions)
	{
		$conditions = array_diff_key($conditions, $this->important_conditions);
		$conditions = array_diff_assoc($conditions, $this->default_conditions);

		return $conditions;
	}

	/**
	 * Renders the view.
	 *
	 * @return mixed
	 */
	public function __invoke()
	{
		$this->validate_access();

		$assets = array('css' => array(), 'js' => array());
		$options = $this->options;

		if (isset($options['assets']))
		{
			$assets = $options['assets'];
		}

		$this->add_assets($this->document, $assets);

		#

		try
		{
// 			$this->fire_render_before(array('id' => $this->id));

			$rc = $this->render_outer_html();

// 			$this->fire_render(array('id' => $this->id, 'rc' => &$rc));

			return $rc;
		}
		catch (\Brickrouge\ElementIsEmpty $e)
		{
			return '';
		}
	}

	/**
	 * Alter the conditions before they are passed to the provider.
	 *
	 * @param array $conditions
	 */
	protected function alter_conditions(array &$conditions)
	{

	}

	/**
	 * Alters template context.
	 *
	 * @param \BlueTihi\Context $context
	 *
	 * @return \BlueTihi\Context
	 */
	protected function alter_context(Context $context)
	{
		$context['pagination'] = '';

		if (isset($context['range']) && isset($context['range']['limit']) && isset($context['range']['count']))
		{
			$range = $context['range'];

			$context['pagination'] = new Pager
			(
				'div', array
				(
					Pager::T_COUNT => $range['count'],
					Pager::T_LIMIT => $range['limit'],
					Pager::T_POSITION => $range['page'],
					Pager::T_WITH => $range['with']
				)
			);
		}

		$context['view'] = $this;

		return $context;
	}

	/**
	 * Adds view's assets to the document.
	 *
	 * @param WdDocument $document
	 * @param array $assets
	 */
	protected function add_assets(Document $document, array $assets=array())
	{
		if (isset($assets['js']))
		{
			foreach ((array) $assets['js'] as $asset)
			{
				list($file, $priority) = (array) $asset + array(1 => 0);

				$document->js->add($file, $priority);
			}
		}

		if (isset($assets['css']))
		{
			foreach ((array) $assets['css'] as $asset)
			{
				list($file, $priority) = (array) $asset + array(1 => 0);

				$document->css->add($file, $priority);
			}
		}
	}

	/**
	 * Fires the `render:before` event on the view using the specified parameters.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	protected function fire_render_before(array $params=array())
	{
		return new View\BeforeRenderEvent($this, $params);
	}

	/**
	 * Returns the placeholder for the empty view.
	 *
	 * @return string
	 */
	protected function render_empty_inner_html()
	{
		global $core;

		$default = I18n\t('The view %name is empty.', [ '%name' => $this->id ]);
		$type = $this->type;
		$module_flat_id = $this->module->flat_id;

		$placeholder = $core->site->metas["$module_flat_id.{$this->type}.placeholder"];

		if ($placeholder)
		{
			return $placeholder;
		}

		$placeholder = $core->site->metas["$module_flat_id.placeholder"];

		if ($placeholder)
		{
			return $placeholder;
		}

		$placeholder = I18n\t('empty_view', [], [

			'scope' => "$module_flat_id.$type",
			'default' => null

		]);

		if ($placeholder)
		{
			return $placeholder;
		}

		$class = get_class($this);

		$default = <<<EOT
<div class="alert undismissable">
	<p>The view is empty, no record was found.</p>

	<ul>
		<li>The registry value <q>$module_flat_id.$type.placeholder</q> was tried, but it does not exists.</li>
		<li>The registry value <q>$module_flat_id.placeholder</q> was tried, but it does not exists.</li>
		<li>The I18n string "empty_view" was tried with the scope "$module_flat_id.$type", but is not defined.</li>
	</ul>

	<p>You can override this alert message by defining one of these registry values or by
	listening to the <code>$class::rescue</code> event.</p>
</div>
EOT;

		return I18n\t('empty_view', [], [

			'scope' => "$module_flat_id.$type",
			'default' => $default

		]);
	}

	/**
	 * Fires {@link View\RescueEvent} using the specified payload.
	 *
	 * @param string $html Reference to the rescued HTML string.
	 *
	 * @return mixed
	 */
	protected function fire_render_empty_inner_html(&$html)
	{
		return new View\RescueEvent($this, $html);
	}

	protected function init_range($count, array $conditions)
	{
		return [

			'page' => $conditions['page'],
			'limit' => $conditions['limit'],
			'count' => $count,
			'with' => $this->filter_conditions($conditions)

		];
	}

	protected $provider;

	protected function get_provider()
	{
		return $this->provider;
	}

	protected function provide($provider, array $conditions)
	{
		if (!($provider instanceof FetcherInterface) && !class_exists($provider))
		{
			throw new \InvalidArgumentException(\ICanBoogie\format
			(
				'Provider class %class for view %id does not exists', array
				(
					'class' => $provider,
					'id' => $this->id
				)
			));
		}

		if ($this->renders == self::RENDERS_ONE)
		{
			$conditions['limit'] = 1;
		}

		$this->provider = $provider = new $provider($this->module->model);

		$records =  $provider($conditions);

		if ($records)
		{
			new BeforeAlterRecordsEvent($this, $records);

			$this->alter_records($records);

			new AlterRecordsEvent($this, $records);
		}

		if ($this->renders == self::RENDERS_ONE)
		{
			return current($records);
		}

		return $records;
	}

	/**
	 * Alter the records provided by the provider.
	 *
	 * @param array $records
	 */
	protected function alter_records(array &$records)
	{

	}

	/**
	 * Renders the inner HTML of the view.
	 *
	 * If the data provided implements {@link \Brickrouge\CSSClassNames}, the class names of the
	 * record are added those of the view element.
	 *
	 * @throws \Exception
	 *
	 * @return string The inner HTML of the view element.
	 */
	protected function render_inner_html($template_path, $engine)
	{
		global $core;

		$view = $this->options;
		$bind = null;
		$id = $this->id;

		$provider_classname = $this->resolve_provider_classname();

		if ($provider_classname)
		{
			list($constructor, $name) = explode('/', $id);

			$conditions = $this->conditions;
			$this->alter_conditions($conditions);

			$bind = $this->provide($provider_classname, $conditions);
			$provider = $this->provider;

			$this->data = $bind;
			$this->range = $this->init_range($provider->count, $provider->conditions + $conditions);

			$engine->context['this'] = $bind;
			$engine->context['range'] = $this->range;

			if (is_array($bind) && current($bind) instanceof Node)
			{
				new \BlueTihi\Context\LoadedNodesEvent($engine->context, $bind);
			}
			else if ($bind instanceof Node)
			{
				new \BlueTihi\Context\LoadedNodesEvent($engine->context, array($bind));
			}
			else if (!$bind)
			{
				$this->element->add_class('empty');

				$html = (string) $this->render_empty_inner_html();

				$this->fire_render_empty_inner_html($html);

				return $html;
			}

			#
			# appending record's css class names to the view element's class.
			#

			if ($bind instanceof \Brickrouge\CSSClassNames)
			{
				$this->element['class'] .= ' ' . $bind->css_class;
			}
		}

		#
		#
		#

		$rc = '';

		if (!$template_path)
		{
			throw new \Exception(\ICanBoogie\format('Unable to resolve template for view %id', array('id' => $id)));
		}

		I18n::push_scope($this->module->flat_id);

		try
		{
			$extension = pathinfo($template_path, PATHINFO_EXTENSION);

			$page = $this->page;
			$module = $core->modules[$this->module_id];

			$engine->context['core'] = $core;
			$engine->context['document'] = $core->document;
			$engine->context['page'] = $page;
			$engine->context['module'] = $module;
			$engine->context['view'] = $this;

			$engine->context = $this->alter_context($engine->context);

			if ('php' == $extension)
			{
				$rc = null;

				ob_start();

				try
				{
					$isolated_require = function ($__file__, $__exposed__)
					{
						extract($__exposed__);

						require $__file__;
					};

					$isolated_require
					(
						$template_path, array
						(
							'bind' => $bind,
							'context' => &$engine->context,
							'core' => $core,
							'document' => $core->document,
							'page' => $page,
							'module' => $module,
							'view' => $this
						)
					);

					$rc = ob_get_clean();
				}
				catch (\ICanBoogie\Exception\Config $e)
				{
					$rc = '<div class="alert">' . $e->getMessage() . '</div>';

					ob_clean();
				}
				catch (\Exception $e)
				{
					ob_clean();

					throw $e;
				}
			}
			else if ('html' == $extension)
			{
				$template = file_get_contents($template_path);

				if ($template === false)
				{
					throw new \Exception("Unable to read template from <q>$template_path</q>");
				}

				$rc = $engine($template, $bind, array('file' => $template_path));

				if ($rc === null)
				{
					var_dump($template_path, file_get_contents($template_path), $rc);
				}
			}
			else
			{
				throw new \Exception(\ICanBoogie\format('Unable to process file %file, unsupported type', array('file' => $template_path)));
			}
		}
		catch (\Exception $e)
		{
			I18n::pop_scope();

			throw $e;
		}

		I18n::pop_scope();

		return $rc;
	}

	protected $element;

	protected function get_element()
	{
		return $this->element;
	}

	protected function alter_element(Element $element)
	{
		return $element;
	}

	/**
	 * Returns the HTML representation of the view element and its content.
	 *
	 * @return string
	 */
	protected function render_outer_html()
	{
		$class = '';
		$type = \ICanBoogie\normalize($this->type);
		$m = $this->module;

		while ($m)
		{
			$normalized_id = \ICanBoogie\normalize($m->id);
			$class = "view--$normalized_id--$type $class";

			$m = $m->parent;
		}

		$this->element = new Element
		(
			'div', array
			(
				'id' => 'view-' . \ICanBoogie\normalize($this->id),
				'class' => trim("view view--$type $class"),
				'data-constructor' => $this->module->id
			)
		);

		$this->element = $this->alter_element($this->element);

// 		\ICanBoogie\log("class: {$this->element->class}, type: $type, assets: " . \ICanBoogie\dump($this->options['assets']));

		$template_path = $this->resolve_template_location();

		#

		$html = $o_html = $this->render_inner_html($template_path, $this->engine);

		new RenderEvent($this, $html);

		#

		if (preg_match('#\.html$#', $this->page->template))
		{
			if (Debug::is_dev())
			{

				$possible_templates = implode(PHP_EOL, $this->template_resolver->templates);

				$html = <<<EOT

<!-- Possible templates for view "{$this->id}":

$possible_templates

-->
$html
EOT;
			}

			$this->element[Element::INNER_HTML] = $html;

			$html = (string) $this->element;
		}

		return $html;
	}

	/**
	 * Returns the template resolver of the view.
	 *
	 * @return TemplateResolver
	 */
	protected function lazy_get_template_resolver()
	{
		return new TemplateResolver($this->id, $this->type, $this->module_id);
	}

	/**
	 * Resolves the template location of the view.
	 *
	 * The template location is resolved using a {@link TemplateResolver} instance.
	 *
	 * @throws \Exception if the template location could not be resolved.
	 *
	 * @return string
	 */
	protected function resolve_template_location()
	{
		$resolver = $this->template_resolver;
		$template = $resolver();

		if (!$template)
		{
			throw new \Exception(\ICanBoogie\format('Unable to resolve template for view %id. Tried: :list', [

				'id' => $this->id,
				':list' => implode("\n<br />", $resolver->templates)

			]));
		}

		return $template;
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
	private function resolve_provider_classname()
	{
		global $core;

		$options = $this->options;
		$classname = empty($options[ViewOptions::PROVIDER_CLASSNAME]) ? null : $options[ViewOptions::PROVIDER_CLASSNAME];

		if (!$classname)
		{
			return;
		}

		if (!empty($options[ViewOptions::MODULE]))
		{
			$resolved_classname = $core->modules->resolve_classname('ViewProvider', $options[ViewOptions::MODULE]);

			if ($classname === ViewOptions::PROVIDER_CLASSNAME_AUTO)
			{
				if (!$resolved_classname)
				{
					throw new \Exception(\ICanBoogie\format("Unable to resolve view provider class name for view %id.", [

						$this->id

					]));
				}

				$classname = $resolved_classname;
			}
			else if ($classname && $classname === $resolved_classname)
			{
				$core->logger->debug(\ICanBoogie\format("The provider class %class can be resolve from the module, it should be left empty in the options: :options", [

					'class' => $classname,
					'options' => $options

				]));
			}
		}

		return $classname;
	}

	/**
	 * Checks if the view access is valid.
	 *
	 * @throws HTTPError when the view access requires authentication.
	 *
	 * @return boolean true
	 */
	protected function validate_access()
	{
		$access_callback = $this->options[self::ACCESS_CALLBACK];

		if ($access_callback && !call_user_func($access_callback, $this))
		{
			throw new AuthenticationRequired;
		}

		return true;
	}
}