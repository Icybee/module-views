# Views 

[![Release](https://img.shields.io/packagist/v/icybee/module-views.svg)](https://github.com/Icybee/module-views/releases)
[![Build Status](https://img.shields.io/travis/Icybee/module-views.svg)](http://travis-ci.org/Icybee/module-views)
[![Code Quality](https://img.shields.io/scrutinizer/g/Icybee/module-views.svg)](https://scrutinizer-ci.com/g/Icybee/module-views)
[![Code Coverage](https://img.shields.io/coveralls/Icybee/module-views.svg)](https://coveralls.io/r/Icybee/module-views)
[![Packagist](https://img.shields.io/packagist/dt/icybee/module-views.svg)](https://packagist.org/packages/icybee/module-views)

The Views module (`views`) allows dynamic data from modules to be displayed in content zones.

Modules usually define three view types: `home`, `list` and `view`. The `home` view displays a
small number of records on an home page. The `list` view displays a list of records and comes with
a pagination to browse through older or newer records. Finally, the `view` view displays the
detail of a record.





## Events





### Views were collected

The `Icybee\Modules\Views\Collection::collect` event of class [Collection\CollectEvent][] is fired
after the views defined by the enabled modules have been collected. Event hooks may used this event
to alter the collection.





### Records fetched by the provided are altered

The `Icybee\Modules\Views\View::alter_records:before` event of class [View\BeforeAlterRecords][]
is fired before the records fetched by the provider are altered by the `alter_records` method
of the view. The `Icybee\Modules\Views\View::alter_records` event of class [View\AlterRecords][]
is fired after the `alter_records` method was called. Event hooks may use these events to alter the
records.





## Events callbacks





### `Icybee\Modules\Pages\SaveOperation::process`

Updates the target page of a view.





## Prototype methods





### `Icybee\Modules\Nodes\Node::url`

Returns the relative URL of a record for the specified view type.





### `Icybee\Modules\Nodes\Node::absolute_url`

Returns the URL of a record for the specified view type.





### `Icybee\Modules\Nodes\Node::get_url`

Returns the relative URL of a record.





### `Icybee\Modules\Node::get_absolute_url`

Returns the URL of a record.





### `Icybee\Modules\Sites\Site::resolve_view_target`

Returns the target page associated with a view.





### `Icybee\Modules\Sites\Site::resolve_view_url`

Returns the URL of a view.





### `ICanBoogie\Application::get_views`

Returns the view collection.





## Markups

### call-view

Displays a view.

```html
<h2>Last articles</h2>
<p:call-view name="articles/home" />
```





----------




## Requirement

The package requires PHP 5.6 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```
$ composer require icybee/module-views
```

Note: This module is part of the modules required by [Icybee](http://icybee.org).





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-views), its repository can be
cloned with the following command line:

	$ git clone https://github.com/Icybee/module-views views





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://img.shields.io/travis/Icybee/module-views.svg)](http://travis-ci.org/Icybee/module-views)
[![Code Coverage](https://img.shields.io/coveralls/Icybee/module-views.svg)](https://coveralls.io/r/Icybee/module-views)





## Documentation

The package is documented as part of the [Icybee](http://icybee.org/) CMS
[documentation](http://icybee.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later by
cleaned with the `make clean` command.





## License

The module is licensed under the New BSD License - See the [LICENSE](LICENSE) file for details.





[Collection\CollectEvent]: http://icybee.org/docs/class-Icybee.Modules.Views.Collection.CollectEvent.html
[View\AlterRecords]: http://icybee.org/docs/class-Icybee.Modules.Views.View.AlterRecords.html
[View\BeforeAlterRecords]: http://icybee.org/docs/class-Icybee.Modules.Views.View.BeforeAlterRecords.html
