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

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\HTTP\Status;

/**
 * Exception throw when a view cannot retrieve a record.
 *
 * @property-read View $view
 */
class NotFound extends \ICanBoogie\HTTP\NotFound
{
	use AccessorTrait;

	/**
	 * @var View
	 */
	private $view;

	/**
	 * @return View
	 */
	protected function get_view()
	{
		return $this->view;
	}

	/**
	 * NotFound constructor.
	 *
	 * @param View $view
	 * @param string $message
	 * @param int $code
	 * @param \Exception|null $previous
	 */
	public function __construct(View $view, $message = '', $code = Status::NOT_FOUND, \Exception $previous = null)
	{
		$this->view = $view;

		parent::__construct($message, $code, $previous);
	}
}
