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
 * Event fired after the view was rendered.
 */
class RenderEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the inner HTML of the view.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * Create an event of type `render`.
	 *
	 * @param \Icybee\Modules\Views\View $target
	 * @param string $html Reference to the inner HTML of the view.
	 */
	public function __construct(\Icybee\Modules\Views\View $target, &$html)
	{
		$this->html = &$html;

		parent::__construct($target, 'render');
	}
}