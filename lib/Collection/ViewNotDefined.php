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
 * Exception thrown when a view is not defined.
 */
class ViewNotDefined extends \RuntimeException
{
	public function __construct($id, $code=500, \Exception $previous=null)
	{
		parent::__construct("View not defined: $id.", $code, $previous);
	}
}