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

use ICanBoogie\ActiveRecord;
use ICanBoogie\Operation;
use ICanBoogie\PropertyNotDefined;
use ICanBoogie\Routing\Pattern;

use Icybee\Modules\Cache\CacheCollection as CacheCollection;
use Icybee\Modules\Nodes\Node;
use Icybee\Modules\Sites\Site;

class Hooks
{
	/**
	 * Synthesizes the `views` config.
	 *
	 * @param array $fragments
	 *
	 * @return array
	 */
	static public function synthesize_config(array $fragments)
	{
		$synthesizer = new ViewConfigSynthesizer;

		return $synthesizer($fragments);
	}

	/*
	 * EVENTS
	 */

	/**
	 * Updates view targets.
	 *
	 * @param Operation\ProcessEvent $event
	 * @param \Icybee\Modules\Pages\Operation\SaveOperation $operation
	 */
	static public function on_page_save(Operation\ProcessEvent $event, \Icybee\Modules\Pages\Operation\SaveOperation $operation)
	{
		$request = $event->request;
		$contents = $request['contents'];
		$editor_ids = $request['editors'];
		$nid = $event->rc['key'];

		if ($editor_ids)
		{
			foreach ($editor_ids as $content_id => $editor_id)
			{
				if ($editor_id != 'view')
				{
					continue;
				}

				if (empty($contents[$content_id]))
				{
					// TODO-20120811: should remove view reference

					continue;
				}

				$content = $contents[$content_id];

				if (strpos($content, '/') !== false)
				{
					$view_target_key = 'views.targets.' . strtr($content, '.', '_');

					\ICanBoogie\app()->site->metas[$view_target_key] = $nid;
				}
			}
		}
	}

	/**
	 * Adds views cache manager to the cache collection.
	 *
	 * @param CacheCollection\CollectEvent $event
	 * @param CacheCollection $collection
	 */
	static public function on_cache_collection_collect(CacheCollection\CollectEvent $event, CacheCollection $collection)
	{
		$event->collection['icybee.views'] = new ViewCacheManager;
	}

	/*
	 * PROTOTYPE
	 */

	static private $pages_model;
	static private $url_cache_by_siteid = [];

	/**
	 * Returns the relative URL of a record for a view type.
	 *
	 * @param ActiveRecord $target
	 * @param string $type View type.
	 *
	 * @return string
	 */
	static public function url(ActiveRecord $target, $type='view')
	{
		$app = \ICanBoogie\app();

		if (self::$pages_model === false)
		{
			#
			# we were not able to get the "pages" model in a previous call, we don't try again.
			#

			return '#';
		}
		else
		{
			try
			{
				self::$pages_model = $app->models['pages'];
			}
			catch (\Exception $e)
			{
				return '#';
			}
		}

		$constructor = isset($target->constructor) ? $target->constructor : $target->model->id;
		$constructor = strtr($constructor, '.', '_');

		$key = 'views.targets.' . $constructor . '/' . $type;
		$site_id = !empty($target->siteid) ? $target->siteid : $app->site_id;

		if (isset(self::$url_cache_by_siteid[$site_id][$key]))
		{
			$pattern = self::$url_cache_by_siteid[$site_id][$key];
		}
		else
		{
			$pattern = false;
			$page_id = null;

			if ($site_id)
			{
				$site = $app->models['sites'][$site_id];
				$page_id = $site->metas[$key];

				if ($page_id)
				{
					$pattern = self::$pages_model[$page_id]->url_pattern;
				}
			}

			self::$url_cache_by_siteid[$site_id][$key] = $pattern;
		}

		if (!$pattern)
		{
			return '#uknown-target-for:' . $constructor . '/' . $type;
		}

		return Pattern::from($pattern)->format($target);
	}

	/**
	 * Return the URL type 'view' for the record.
	 *
	 * @param ActiveRecord $record
	 *
	 * @return string
	 */
	static public function get_url(ActiveRecord $record)
	{
		return $record->url('view');
	}

	/**
	 * Return the absolute URL type for the node.
	 *
	 * @param ActiveRecord $record
	 * @param string $type The URL type.
	 *
	 * @return string
	 */
	static public function absolute_url(ActiveRecord $record, $type='view')
	{
		$app = \ICanBoogie\app();

		try
		{
			$site = $record->site ? $record->site : $app->site;
		}
		catch (PropertyNotDefined $e)
		{
			$site = $app->site;
		}

		return $site->url . substr($record->url($type), strlen($site->path));
	}

	/**
	 * Return the _primary_ absolute URL for the node.
	 *
	 * @param ActiveRecord $record
	 *
	 * @return string The primary absolute URL for the node.
	 */
	static public function get_absolute_url(ActiveRecord $record)
	{
		return $record->absolute_url('view');
	}

	static private $view_target_cache = [];

	/**
	 * Returns the target page of a view.
	 *
	 * @param Site $site
	 * @param string $view_id Identifier of the view.
	 *
	 * @return \Icybee\Modules\Pages\Page
	 */
	static public function resolve_view_target(Site $site, $view_id)
	{
		if (isset(self::$view_target_cache[$view_id]))
		{
			return self::$view_target_cache[$view_id];
		}

		$target_id = $site->metas['views.targets.' . strtr($view_id, '.', '_')];

		return self::$view_target_cache[$view_id] = $target_id
			? \ICanBoogie\app()->models['pages'][$target_id]
			: false;
	}

	static private $view_url_cache = [];

	/**
	 * Returns the URL of a view.
	 *
	 * @param Site $site
	 * @param string $view_id The identifier of the view.
	 * @param array|null $args The arguments to format the URL, if the URL uses a pattern.
	 *
	 * @return string
	 * @throws \Exception
	 */
	static public function resolve_view_url(Site $site, $view_id, $args=null)
	{
		if (isset(self::$view_url_cache[$view_id]))
		{
			return self::$view_url_cache[$view_id];
		}

		$target = $site->resolve_view_target($view_id);

		if (!$target)
		{
			return '#unknown-target-for-view-' . $view_id;
		}

		$url_pattern = $target->url_pattern;

		if (!Pattern::is_pattern($url_pattern))
		{
			return self::$view_url_cache[$view_id] = $target->url;
		}

		return Pattern::from($url_pattern)->format($args);
	}

	/*
	 * MARKUPS
	 */

	/**
	 * Renders the specified view.
	 *
	 * @param array $args
	 * @param mixed $engine
	 * @param mixed $template
	 *
	 * @return mixed
	 */
	static public function markup_call_view(array $args, $engine, $template)
	{
		return \ICanBoogie\app()->editors['view']->render($args['name'], $engine, $template);
	}
}
