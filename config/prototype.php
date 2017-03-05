<?php

namespace Icybee\Modules\Views;

$hooks = Hooks::class . '::';

return [

	'Icybee\Modules\Nodes\Node::url' => $hooks . 'url',
	'Icybee\Modules\Nodes\Node::absolute_url' => $hooks . 'absolute_url',
	'Icybee\Modules\Nodes\Node::lazy_get_url' => $hooks . 'get_url',
	'Icybee\Modules\Nodes\Node::lazy_get_absolute_url' => $hooks . 'get_absolute_url',

	'Icybee\Modules\Users\User::url' => $hooks . 'url',
	'Icybee\Modules\Users\User::absolute_url' => $hooks . 'absolute_url',
	'Icybee\Modules\Users\User::lazy_get_url' => $hooks . 'get_url',
	'Icybee\Modules\Users\User::lazy_get_absolute_url' => $hooks . 'get_absolute_url',

	'Icybee\Modules\Sites\Site::resolve_view_target' => $hooks . 'resolve_view_target',
	'Icybee\Modules\Sites\Site::resolve_view_url' => $hooks . 'resolve_view_url',
	'ICanBoogie\Application::lazy_get_views' => Collection::class . '::get'

];
