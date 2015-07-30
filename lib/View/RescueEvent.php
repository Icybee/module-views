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

use Icybee\Modules\Views\View;

/**
 * Event fired when the view inner HTML is empty.
 */
class RescueEvent extends Event
{
	/**
	 * Reference to the rescued HTML.
	 *
	 * @var string
	 */
	public $html;

	public function __construct(View $target, &$html)
	{
		$this->html = &$html;

		parent::__construct($target, 'rescue');
	}
}
