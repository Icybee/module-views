<?php

namespace Icybee\Modules\Views;

$hooks = Hooks::class . '::';

return [

	'events' => [

		'Icybee\Modules\Pages\SaveOperation::process' => $hooks . 'on_page_save',
		'Icybee\Modules\Cache\CacheCollection::collect' => $hooks . 'on_cache_collection_collect',
		'Icybee\Modules\Modules\ActivateOperation::process' => CacheManager::class . '::revoke',
		'Icybee\Modules\Modules\DeactivateOperation::process' => CacheManager::class . '::revoke'

	],

	'prototypes' => [

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
		'ICanBoogie\Core::lazy_get_views' => Collection::class . '::get'

	],

	'patron.markups' => [

		'call-view' => [

			$hooks . 'markup_call_view', [

				'name' => [ 'required' => true ]

			]

		]

	]

];
