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

use ICanBoogie\Module\Modules;
use ICanBoogie\OffsetNotWritable;

/**
 * A collection of view definitions.
 */
class Collection implements \ArrayAccess, \IteratorAggregate
{
	private static $instance;

	/**
	 * Returns a unique instance.
	 *
	 * @return Collection
	 */
	static public function get()
	{
		if (self::$instance)
		{
			return self::$instance;
		}

		return self::$instance = new static;
	}

	protected $collection;

	protected function __construct()
	{
		$app = \ICanBoogie\app();

		if ($app->config['cache views'])
		{
			$collection = $app->vars['cached_views'];

			if (!$collection)
			{
				$collection = $this->collect($app->modules);

				$app->vars['cached_views'] = $collection;
			}
		}
		else
		{
			$collection = $this->collect($app->modules);
		}

		$this->collection = $collection;
	}

	/**
	 * Collects views defined by modules.
	 *
	 * After the views defined by modules have been collected {@link Collection\CollectEvent} is
	 * fired.
	 *
	 * @param Modules $modules
	 *
	 * @return array[string]array
	 *
	 * @throws \UnexpectedValueException when the {@link ViewOptions::TITLE},
	 * {@link ViewOptions::TYPE}, {@link ViewOptions::MODULE} or {@link ViewOptions::RENDERS}
	 * properties are empty.
	 */
	protected function collect(Modules $modules)
	{
		static $required = [

			ViewOptions::TITLE,
			ViewOptions::TYPE,
			ViewOptions::MODULE,
			ViewOptions::RENDERS

		];

		$collection = array();

		foreach ($modules->enabled_modules_descriptors as $id => $descriptor)
		{
			$module = $modules[$id];

			if (!$module->has_property('views'))
			{
				continue;
			}

			$module_views = $module->views;

			foreach ($module_views as $type => $definition)
			{
				$definition += [

					ViewOptions::MODULE => $id,
					ViewOptions::TYPE => $type

				];

				$collection[$id . '/' . $type] = $definition;
			}
		}

		new Collection\CollectEvent($this, $collection);

		foreach ($collection as $id => &$definition)
		{
			$definition += [

				ViewOptions::ACCESS_CALLBACK => null,
				ViewOptions::CLASSNAME => null,
				ViewOptions::PROVIDER_CLASSNAME => null,
				ViewOptions::TITLE_ARGS => []

			];

			foreach ($required as $property)
			{
				if (empty($definition[$property]))
				{
					throw new \UnexpectedValueException(\ICanBoogie\format
					(
						'%property is empty for the view %id.', array
						(
							'property' => $property,
							'id' => $id
						)
					));
				}
			}
		}

		return $collection;
	}

	/**
	 * Checks if a view exists.
	 */
	public function offsetExists($offset)
	{
		return isset($this->collection[$offset]);
	}

	/**
	 * Returns the definition of a view.
	 */
	public function offsetGet($id)
	{
		if (!$this->offsetExists($id))
		{
			throw new Collection\ViewNotDefined($id);
		}

		return $this->collection[$id];
	}

	/**
	 * @throws OffsetNotWritable in attempt to set an offset.
	 */
	public function offsetSet($offset, $value)
	{
		throw new OffsetNotWritable(array($offset, $this));
	}

	/**
	 * @throws OffsetNotWritable in attempt to unset an offset.
	 */
	public function offsetUnset($offset)
	{
		throw new OffsetNotWritable(array($offset, $this));
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->collection);
	}
}
