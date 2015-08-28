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
	 * Directives prefix.
	 */
	const DIRECTIVE_PREFIX = '@';

	/**
	 * Defines the identifier of the module from which views are inherited.
	 */
	const DIRECTIVE_INHERITS = self::DIRECTIVE_PREFIX . 'inherits';

	/**
	 * Defines the path to the config directory.
	 */
	const DIRECTIVE_PATH = self::DIRECTIVE_PREFIX . 'path';

	/**
	 * Defines a callable used to arbiter the access to the view.
	 *
	 * The callable returns `true` if the access is granted, `false` otherwise. It is recommended
	 * to throw an appropriate exception should the access be refused.
	 */
	const ACCESS_CALLBACK = 'access_callback';

	/**
	 * Defines the CSS and JavaScript assets required by the view.
	 */
	const ASSETS = 'assets';

	/**
	 * Defines the class used to instantiate the view.
	 *
	 * If {@link MODULE} is defined the class can be resolved using the
	 * {@link \ICanBoogie\Module\Modules::resolve_classname()}  method with the `View` basename.
	 */
	const CLASSNAME = 'class';

	/**
	 * Important conditions, which cannot be overridden by the user.
	 */
	const CONDITIONS = 'conditions';

	/**
	 * Initial conditions for the provider, which can be overridden by the user.
	 */
	const DEFAULT_CONDITIONS = 'default_conditions';

	/**
	 * Defines the module providing the view.
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
	 */
	const PROVIDER_CLASSNAME = 'provider';
	const PROVIDER_CLASSNAME_AUTO = true;

	/**
	 * Defines the number of records the view is rendering. Possible values are
	 * {@link RENDERS_ONE}, {@link RENDERS_MANY}, and {@link RENDERS_OTHER} when to number of
	 * records is irrelevant.
	 */
	const RENDERS = 'renders';
	const RENDERS_ONE = 1;
	const RENDERS_MANY = 2;
	const RENDERS_OTHER = 3;

	/**
	 * Defines the title of the view, which can be displayed in the admin.
	 */
	const TITLE = 'title';

	/**
	 * Defines the arguments used to format the title.
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
	 */
	const TYPE = 'type';

	/**
	 * Normalizes options.
	 *
	 * @param array $options
	 *
	 * @return array Normalized options.
	 */
	static public function normalize(array $options)
	{
		$options = array_filter($options, function($v) {

			return $v !== null && $v !== [];

		});

		return $options + [

			self::ACCESS_CALLBACK => null,
			self::ASSETS => [],
			self::CONDITIONS => [],
			self::DEFAULT_CONDITIONS => [],
			self::MODULE => null,
			self::PROVIDER_CLASSNAME => null,
			self::RENDERS => null,
			self::TITLE => null,
			self::TITLE_ARGS => [],
			self::TYPE => null

		];
	}

	private function __construct() {}
}
