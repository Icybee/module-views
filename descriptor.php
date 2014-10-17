<?php

namespace Icybee\Modules\Views;

use ICanBoogie\Module\Descriptor;

return array
(
	Descriptor::CATEGORY => 'features',
	Descriptor::DESCRIPTION => 'Allows dynamic data from modules to be displayed in content zones.',
	Descriptor::REQUIRED => true,
	Descriptor::REQUIRES => array
	(
		'pages' => '1.0'
	),

	Descriptor::NS => __NAMESPACE__,
	Descriptor::TITLE => 'Views',
	Descriptor::VERSION => '1.0'
);
