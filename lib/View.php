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

use Brickrouge\ElementIsEmpty;
use Brickrouge\Pagination;
use ICanBoogie\Facets\FetcherInterface;
use ICanBoogie\AuthenticationRequired;
use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Facets\RecordCollection;
use ICanBoogie\I18n;
use ICanBoogie\Module;
use ICanBoogie\Object;

use Brickrouge\Document;
use Brickrouge\Element;
use Brickrouge\Pager;

use BlueTihi\Context;

use ICanBoogie\Render\TemplateNotFound;
use Icybee\Modules\Nodes\Node;
use Icybee\Modules\Views\View\RenderEvent;
use Icybee\Modules\Views\View\AlterRecordsEvent;
use Icybee\Modules\Views\View\BeforeAlterRecordsEvent;

/**
 * A view on provided data.
 *
 * @property-read \ICanBoogie\Core $app
 * @property-read string $id The identifier of the view.
 * @property-read mixed $data The data provided by the view's provider.
 * @property-read array $default_conditions Default conditions.
 * @property-read array $user_conditions User conditions, overwriting default conditions.
 * @property-read array $important_conditions Important conditions, overwriting user conditions.
 * @property-read array $conditions Conditions resolved from the _default_, _user_, and _important_ conditions.
 */
class View extends Object
{
	/**
	 * @deprecated
	 */
	const ACCESS_CALLBACK = 'access_callback';
	/**
	 * @deprecated
	 */
	const ASSETS = 'assets';
	/**
	 * @deprecated
	 */
	const CLASSNAME = 'class';
	/**
	 * @deprecated
	 */
	const CONDITIONS = 'conditions';
	/**
	 * @deprecated
	 */
	const DEFAULT_CONDITIONS = 'default_conditions';
	/**
	 * @deprecated
	 */
	const PROVIDER = 'provider';
	/**
	 * @deprecated
	 */
	const RENDERS = 'renders';
	/**
	 * @deprecated
	 */
	const RENDERS_ONE = 1;
	/**
	 * @deprecated
	 */
	const RENDERS_MANY = 2;
	/**
	 * @deprecated
	 */
	const RENDERS_OTHER = 3;
	/**
	 * @deprecated
	 */
	const TITLE = 'title';

	protected $id;

	protected function get_id()
	{
		return $this->id;
	}

	/**
	 * The amount of data the view is rendering.
	 *
	 * - {@link ViewOptions::RENDERS_ONE}: Renders a record.
	 *
	 * - {@link ViewOptions::RENDERS_MANY}: Renders a collection of records. A 'range' value is added to the rendering
	 * context the following properties:
	 *     - (int) limit: The maximum number of record to render.
	 *     - (int) page: The starting page.
	 *     - (int) count: The total number of records. This value is to be entered by the provider.
	 *
	 * - {@link ViewOptions::RENDERS_OTHER}: Renders an unknown amount of data.
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
		if (isset($this->module))
		{
			return $this->module;
		}

		return $this->app->modules[$this->module_id];
	}

	private $data;

	protected function get_data()
	{
		return $this->data;
	}

	public $module_id;
	public $type;

	private $template_tries = [];
	private $template_pathname;

	public function __construct($id, array $options, $engine, $document, $page, $template = null)
	{
		unset($this->module);

		$this->options = $options + [

			ViewOptions::CONDITIONS => [],
			ViewOptions::DEFAULT_CONDITIONS => []

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
		if ($this->renders == ViewOptions::RENDERS_ONE)
		{
			$limit = 1;
		}
		else
		{
			$limit = $this->page->site->metas[$this->module->flat_id . '.limits.' . $this->type] ?: null;
		}

		return $this->options[ViewOptions::DEFAULT_CONDITIONS] + [

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
		return $this->page->url_variables + $this->options[ViewOptions::CONDITIONS];
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
	 *
	 * @return array
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

		$assets = [ 'css' => [], 'js' => [] ];
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
		catch (ElementIsEmpty $e)
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
	 * Alters context.
	 *
	 * @param Context $context
	 *
	 * @return Context
	 */
	protected function alter_context(Context $context)
	{
		$data = $this->data;
		$context['this'] = $data;
		$context['view'] = $this;
		$context['pagination'] = '';

		if ($data instanceof RecordCollection)
		{
			$this->alter_context_with_records($context, $data);
		}

		return $context;
	}

	/**
	 * Alters context with records.
	 *
	 * The methods adds the `range` and `pagination` properties.
	 *
	 * @param Context $context
	 * @param RecordCollection $records
	 */
	protected function alter_context_with_records(Context $context, RecordCollection $records)
	{
		$count = $records->total_count;
		$limit = $records->limit;
		$page = $records->page;
		$conditions = $this->filter_conditions($records->conditions);

		$context['range'] = [

			'count' => $count,
			'limit' => $limit,
			'page'=> $page,
			'with' => $conditions

		];

		$context['pagination'] = new Pagination([

			Pagination::COUNT => $count,
			Pagination::LIMIT => $limit,
			Pagination::POSITION => $page,
			Pagination::WITH => $conditions

		]);
	}

	/**
	 * Adds view's assets to the document.
	 *
	 * @param Document $document
	 * @param array $assets
	 */
	protected function add_assets(Document $document, array $assets = [])
	{
		if (isset($assets['js']))
		{
			foreach ((array) $assets['js'] as $asset)
			{
				list($file, $priority) = (array) $asset + [ 1 => 0 ];

				$document->js->add($file, $priority);
			}
		}

		if (isset($assets['css']))
		{
			foreach ((array) $assets['css'] as $asset)
			{
				list($file, $priority) = (array) $asset + [ 1 => 0 ];

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
	protected function fire_render_before(array $params = [])
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
		$site = $this->app->site;
		$type = $this->type;
		$module_flat_id = $this->module->flat_id;

		$placeholder = $site->metas["$module_flat_id.{$this->type}.placeholder"];

		if ($placeholder)
		{
			return $placeholder;
		}

		$placeholder = $site->metas["$module_flat_id.placeholder"];

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

	protected $provider;

	protected function get_provider()
	{
		return $this->provider;
	}

	protected function provide($provider, array $conditions)
	{
		if (!($provider instanceof FetcherInterface) && !class_exists($provider))
		{
			throw new \InvalidArgumentException(\ICanBoogie\format('Provider class %class for view %id does not exists', [

				'class' => $provider,
				'id' => $this->id

			]));
		}

		if ($this->renders == ViewOptions::RENDERS_ONE)
		{
			$conditions['limit'] = 1;
		}

		$this->provider = $provider = new $provider($this->module->model);

		$records = $provider($conditions);

		if ($records)
		{
			new BeforeAlterRecordsEvent($this, $records);

			$this->alter_records($records);

			new AlterRecordsEvent($this, $records);
		}

		if ($this->renders == ViewOptions::RENDERS_ONE)
		{
			return $records->one;
		}

		return $records;
	}

	/**
	 * Alter the records provided by the provider.
	 *
	 * @param RecordCollection $records
	 */
	protected function alter_records($records)
	{

	}

	/**
	 * Renders the inner HTML of the view.
	 *
	 * If the data provided implements {@link \Brickrouge\CSSClassNames}, the class names of the
	 * record are added those of the view element.
	 *
	 * @param Context $context
	 *
	 * @throws \Exception
	 *
	 * @return string The inner HTML of the view element.
	 */
	protected function render_inner_html(Context $context)
	{
		$this->data = $bind = $this->resolve_bind();

		if (!$bind && $this->renders != ViewOptions::RENDERS_OTHER)
		{
			$this->element->add_class('empty');

			$html = (string) $this->render_empty_inner_html();

			$this->fire_render_empty_inner_html($html);

			return $html;
		}

		if (is_array($bind) && reset($bind) instanceof Node)
		{
			new \BlueTihi\Context\LoadedNodesEvent($context, $bind);
		}
		elseif ($bind instanceof Node)
		{
			new \BlueTihi\Context\LoadedNodesEvent($context, [ $bind ]);
		}
		elseif ($bind instanceof \Brickrouge\CSSClassNames)
		{
			$this->element['class'] .= ' ' . $bind->css_class;
		}

		/* @var $template_resolver \ICanBoogie\Render\TemplateResolver */
		$template_resolver = clone $this->app->template_resolver;
		$engines = $this->app->template_engines;

		$tries = [];
		$this->template_tries = &$tries;
		$template_pathname = $template_resolver->resolve($this->id, $engines->extensions, $tries);

		if (!$template_pathname)
		{
			throw new TemplateNotFound("Unable to find template for $this->id.", $tries);
		}

		$this->template_pathname = $template_pathname;

		I18n::push_scope($this->module->flat_id);

		try
		{
			$html = $engines->render($template_pathname, $bind, $this->resolve_variables());

			I18n::pop_scope();

			return $html;
		}
		catch (\Exception $e)
		{
			I18n::pop_scope();

			throw $e;
		}
	}

	protected function resolve_bind()
	{
		$provider_classname = $this->resolve_provider_classname();

		if (!$provider_classname)
		{
			return null;
		}

		$conditions = $this->conditions;
		$this->alter_conditions($conditions);

		return $this->provide($provider_classname, $conditions);
	}

	protected function resolve_variables()
	{
		$variables = $this->alter_context($this->engine->context)->to_array();

		unset($variables['this']);
		unset($variables['self']);

		$app = $this->app;

		return $variables + [

			'core' => $app,
			'app' => $app,
			'document' => $app->document,
			'module' => $this->module,
			'view' => $this

		];
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

		$this->element = new Element('div', [

			'id' => 'view-' . \ICanBoogie\normalize($this->id),
			'class' => trim("view view--$type $class"),
			'data-constructor' => $this->module->id

		]);

		$this->element = $this->alter_element($this->element);

		#

		$html = $o_html = $this->render_inner_html($this->engine->context);

		new RenderEvent($this, $html);

		#

		if (preg_match('#\.html$#', $this->page->template))
		{
			if (Debug::is_dev())
			{

				$possible_templates = implode(PHP_EOL, $this->template_tries);

				$html = <<<EOT

<!-- Possible templates for view "{$this->id}":

$possible_templates

-->
$html
EOT;
			}

			$this->element[Element::INNER_HTML] = $html;
			$this->element['data-template-path'] = $this->template_pathname;

			$html = (string) $this->element;
		}

		return $html;
	}

	/**
	 * Resolves the name of the class that should be used to instantiate the view.
	 *
	 * If `module` is specified in the view definition, the name is resolved according to the
	 * module's hierarchy.
	 *
	 * @return string The class that should be used to instantiate the view.
	 *
	 * @throws \Exception
	 */
	private function resolve_provider_classname()
	{
		$options = $this->options;
		$classname = empty($options[ViewOptions::PROVIDER_CLASSNAME]) ? null : $options[ViewOptions::PROVIDER_CLASSNAME];

		if (!$classname)
		{
			return null;
		}

		$app = $this->app;

		if (!empty($options[ViewOptions::MODULE]))
		{
			$resolved_classname = $app->modules->resolve_classname('ViewProvider', $options[ViewOptions::MODULE]);

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
				$app->logger->debug(\ICanBoogie\format("The provider class %class can be resolved from the module, it is recommended to PROVIDER_CLASSNAME_AUTO in the options: :options", [

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
	 * @return bool true
	 *
	 * @throws AuthenticationRequired when the view access requires authentication.
	 */
	protected function validate_access()
	{
		$access_callback = $this->options[ViewOptions::ACCESS_CALLBACK];

		if ($access_callback && !call_user_func($access_callback, $this))
		{
			throw new AuthenticationRequired;
		}

		return true;
	}
}
