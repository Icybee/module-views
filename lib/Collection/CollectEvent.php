<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Views\Collection;

/**
 * Event class for the `Icybee\Modules\Views\Collection::collect` event.
 *
 * Event hooks may use this event to alter the view collection.
 */
class CollectEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the collection to alter.
	 *
	 * @var array[string]array
	 */
	public $collection;

	/**
	 * The event is constructed with the type 'collect'.
	 *
	 * @param \Icybee\Modules\Views\Collection $target
	 * @param array $collection Reference to the view collection.
	 */
	public function __construct(\Icybee\Modules\Views\Collection $target, &$collection)
	{
		$this->collection = &$collection;

		parent::__construct($target, 'collect');
	}
}