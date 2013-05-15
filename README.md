# Views

The Views module (`views`) allows dynamic data from modules to be displayed in content zones.

Modules usually define three view types: `home`, `list` and `view`. The `home` view displays a
small number of records on an home page. The `list` view displays a list of records and comes with
a pagination to browse through older or newer records. Finally, the `view` view displays the
detail of a record.





## Events

### Icybee\Modules\Views\Collection\CollectEvent

Fired after the views defined by the enabled modules have been collected. Allows third parties to
alter the collection.



### Icybee\Modules\Views\ActiveRecordProvider\BeforeAlterConditionsEvent

Fired before `alter_conditions` is invoked.



### Icybee\Modules\Views\ActiveRecordProvider\AlterConditionsEvent

Fired after `alter_conditions` was invoked.



### Icybee\Modules\Views\ActiveRecordProvider\BeforeAlterQueryEvent

Fired before `alter_query` is invoked.



### Icybee\Modules\Views\ActiveRecordProvider\AlterQueryEvent

Fired after `alter_query` was invoked.



### Icybee\Modules\Views\ActiveRecordProvider\BeforeAlterContextEvent

Fired before `alter_context` is invoked.



### Icybee\Modules\Views\ActiveRecordProvider\AlterContextEvent

Fired after `alter_context` was invoked.



### Icybee\Modules\Views\ActiveRecordProvider\AlterResultEvent

Fired after `extract_result` was invoked.




## Events callbacks


### ICanBoogie\Modules\Pages\SaveOperation::process

Updates the target page of a view.




## Prototype methods

### ICanBoogie\ActiveRecord\Node::url

Returns the relative URL of a record for the specified view type.



### ICanBoogie\ActiveRecord\Node::absolute_url

Returns the URL of a record for the specified view type.



### ICanBoogie\ActiveRecord\Node::get_url

Returns the relative URL of a record.



### ICanBoogie\ActiveRecord\Node::get_absolute_url

Returns the URL of a record.



### ICanBoogie\ActiveRecord\Site::resolve_view_target

Returns the target page associated with a view.



### ICanBoogie\ActiveRecord\Site::resolve_view_url

Returns the URL of a view.



### ICanBoogie\Core::get_views

Returns the view collection.




## Markups

### call-view

Displays a view.

```html
<h2>Last articles</h2>
<p:call-view name="articles/home" />
```





## Requirement

The package requires PHP 5.3 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",
	"require":
	{
		"icybee/module-views": "*"
	}
}
```

Note: This module is part of the modules required by [Icybee](http://icybee.org).





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-views), its repository can be
cloned with the following command line:

	$ git clone git://github.com/Icybee/module-views.git views





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://travis-ci.org/Icybee/module-views.png?branch=master)](https://travis-ci.org/Icybee/module-views)





## Documentation

The package is documented as part of the [Icybee](http://icybee.org/) CMS
[documentation](http://icybee.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later by
cleaned with the `make clean` command.





## License

The module is licensed under the New BSD License - See the LICENSE file for details.