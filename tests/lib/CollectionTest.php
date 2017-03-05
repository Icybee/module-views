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

class CollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testGet()
	{
		$collection = Collection::get();

		$this->assertInstanceOf(Collection::class, $collection);
	}
}
