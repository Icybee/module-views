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
 * Event fired before the view is rendered.
 */
class BeforeRenderEvent extends Event
{
	public function __construct(View $target, array $payload)
	{
		parent::__construct($target, 'render:before', $payload);
	}
}
