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

use ICanBoogie\Event;
use ICanBoogie\Facets\RecordCollection;

use Icybee\Modules\Views\View;

/**
 * Event class for the `Icybee\Modules\Views\View::alter_records:before` event.
 *
 * Event hooks may use this event to alter the records provided to the view, before its
 * `alter_records` method is invoked.
 */
class BeforeAlterRecordsEvent extends Event
{
	const TYPE = 'alter_records:before';

	/**
	 * Reference to the records.
	 *
	 * @var RecordCollection
	 */
	public $records;

	/**
	 * The event is constructed with the type `alter_records:before`.
	 *
	 * @param View $target
	 * @param RecordCollection $records Reference to the records.
	 */
	public function __construct(View $target, RecordCollection &$records)
	{
		$this->records = &$records;

		parent::__construct($target, self::TYPE);
	}
}
