<?php

namespace Icybee\Modules\Views;

$hooks = Hooks::class . '::';

return [

	'patron.markups' => [

		'call-view' => [

			$hooks . 'markup_call_view', [

				'name' => [ 'required' => true ]

			]

		]

	]

];
