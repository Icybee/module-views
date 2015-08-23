<?php

namespace Icybee\Modules\Views;

use Icybee;

$hooks = Hooks::class . '::';

return [

	Icybee\Modules\Pages\SaveOperation::class . '::process' => $hooks . 'on_page_save',
	Icybee\Modules\Cache\CacheCollection::class . '::collect' => $hooks . 'on_cache_collection_collect',
	Icybee\Modules\Modules\ActivateOperation::class . '::process' => ViewCacheManager::class . '::revoke',
	Icybee\Modules\Modules\DeactivateOperation::class . '::process' => ViewCacheManager::class . '::revoke'

];
