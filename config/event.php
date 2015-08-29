<?php

namespace Icybee\Modules\Views;

use Icybee;

$hooks = Hooks::class . '::';

return [

	Icybee\Modules\Pages\Operation\SaveOperation::class . '::process' => $hooks . 'on_page_save',
	Icybee\Modules\Cache\CacheCollection::class . '::collect' => $hooks . 'on_cache_collection_collect',
	Icybee\Modules\Modules\Operation\ActivateOperation::class . '::process' => ViewCacheManager::class . '::revoke',
	Icybee\Modules\Modules\Operation\DeactivateOperation::class . '::process' => ViewCacheManager::class . '::revoke'

];
