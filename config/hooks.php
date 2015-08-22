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

	'patron.markups' => [

		'call-view' => [

			$hooks . 'markup_call_view', [

				'name' => [ 'required' => true ]

			]

		]

	]

];
