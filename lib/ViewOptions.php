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

final class ViewOptions
{
	/**
	 * Defines a callable used to arbiter the access to the view.
	 *
	 * The callable returns `true` if the access is granted, `false` otherwise. It is recommended
	 * to throw an appropriate exception should the access be refused.
	 *
	 * @var callable
	 */
	const ACCESS_CALLBACK = 'access_callback';

	/**
	 * Defines the CSS and JavaScript assets required by the view.
	 *
	 * @var array
	 */
	const ASSETS = 'assets';

	/**
	 * Defines the class used to instantiate the view.
	 *
	 * If {@link MODULE} is defined the class can be resolved using the
	 * {@link \ICanBoogie\Module\Modules::resolve_classname()}  method with the `View` basename.
	 *
	 * @var string
	 */
	const CLASSNAME = 'class';

	/**
	 * Important conditions, which cannot be overridden by the user.
	 *
	 * @var array
	 */
	const CONDITIONS = 'conditions';

	/**
	 * Initial conditions for the provider, which can be overridden by the user.
	 *
	 * @var array
	 */
	const DEFAULT_CONDITIONS = 'default_conditions';

	/**
	 * Defines the module providing the view.
	 *
	 * @var string|\ICanBoogie\Module
	 */
	const MODULE = 'module';

	/**
	 * Defines the class used to instantiate the view provider.
	 *
	 * If {@link MODULE} is defined the class can be resolved using the
	 * {@link \ICanBoogie\Module\Modules::resolve_classname()} method with the `ViewProvider`
	 * basename. But because a provider is not required for a valid view,
	 * {@link PROVIDER_CLASSNAME_AUTO} need to be used to specify that the provider class
	 * should be resolved.
	 *
	 * @var string
	 */
	const PROVIDER_CLASSNAME = 'provider';
	const PROVIDER_CLASSNAME_AUTO = true;

	/**
	 * Defines the number of records the view is rendering. Possible values are
	 * {@link RENDERS_ONE}, {@link RENDERS_MANY}, and {@link RENDERS_OTHER} when to number of
	 * records is irrelevant.
	 *
	 * @var int
	 */
	const RENDERS = 'renders';
	const RENDERS_ONE = 1;
	const RENDERS_MANY = 2;
	const RENDERS_OTHER = 3;

	/**
	 * Defines the title of the view, which can be displayed in the admin.
	 *
	 * @var string
	 */
	const TITLE = 'title';

	/**
	 * Defines the arguments used to format the title.
	 *
	 * @var array
	 */
	const TITLE_ARGS = 'title args';

	/**
	 * General type of the view.
	 *
	 * The following types are commonly used, and more can be defined:
	 *
	 * - `home`: A list of records in a block on the home page.
	 * - `list`: A list of records.
	 * - `view`: A record.
	 *
	 * @var string
	 */
	const TYPE = 'type';

	private function __construct() {}
}