<?php

namespace Icybee\Modules\Views;

use ICanBoogie\Module\Descriptor;

return [

	Descriptor::CATEGORY => 'features',
	Descriptor::DESCRIPTION => "Allows dynamic data from modules to be displayed in content zones.",
	Descriptor::REQUIRED => true,
	Descriptor::REQUIRES => [ 'pages' ],
	Descriptor::NS => __NAMESPACE__,
	Descriptor::TITLE => "Views"

];
