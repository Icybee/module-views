<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Views\View;

/**
 * Event class for the `Icybee\Modules\Views\View::alter_records` event.
 *
 * Event hooks may use this event to alter the records provided to the view, after its
 * `alter_records` method was invoked.
 */
class AlterRecordsEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the records.
	 *
	 * @var array
	 */
	public $records;

	/**
	 * The event is constructed with the type `alter_records`.
	 *
	 * @param \Icybee\Modules\Views\View $target
	 * @param array $records Reference to the records.
	 */
	public function __construct(\Icybee\Modules\Views\View $target, &$records)
	{
		$this->records = &$records;

		parent::__construct($target, 'alter_records');
	}
}