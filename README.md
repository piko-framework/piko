# Piko

Piko is a Web micro Framework inspired by Yii2 Framework to build MVC applications.
It requires no dependencies and should be sufficient for simple projects.

The framework approach is modular : each request is dispatched to internal route which corresponds to a module,
a controller and a controller action.

Each component of the framework is an event manager. It's possible to inject custom code when events are triggered.

The view rendering is done in two stages : one for the global layout and one for the controller action rendering.

Also, the framework offers simple ways to manipulate databases and to manage users.

## Installation via composer

```bash
composer require ilhooq/piko
```

## Getting started

A trivial example :

```php
<?php
use piko\Application;
use piko\Module;
use piko\Controller;

require('vendor/autoload.php');

class SiteModule extends Module
{

}

class HelloController extends Controller
{
    public $layout = false;

    public function indexAction()
    {
        return 'Hello World!';
    }

    public function index2Action()
    {
        return 'Hello ' . (isset($_GET['name'])? $_GET['name'] : 'Unknown') . '!' ;
    }
    
    public function index3Action()
    {
        return 'Hello guy!';
    }
}

$config = [
    'basePath' => __DIR__,
    'components' => [
        'router' => [
            'class' => 'piko\Router',
            'routes' => [
                '^/$' => 'site/hello/index',
                '^/hello/(\w+)' => 'site/hello/index2|name=$1',
                '^/(\w+)/(\w+)/(\w+)' => '$1/$2/$3'
            ],
        ]
    ],
    'modules' => [
        'site' => [
            'class' => 'SiteModule',
            'controllerMap' => [
                'hello' => 'HelloController'
            ]
         ]
    ]
];


(new Application($config))->run();
```

To quickly test, you can use the php builtin web server:

```bash
php -S localhost:8080
```

If you go to `http://localhost:8080/` you will get the the message "Hello World!". The router associates the uri / to the route `site/hello/index`.
(`'^/$' => 'site/hello/index'`) in the configuration.

The route is composed with a module identifier (`site`), a controller identifier (`hello`) and a controller action (`index`)

Also it's possible to transmit parameters to a route. If you go to `http://localhost:8080/hello/martin` you will get the the message "Hello martin!".
The router associate the uri /hello/(\w+) to the route `site/hello/index2|name=$1` and populate the global $_GET array with the `name` parameter.

Finally, the uri can math the route with `'^/(\w+)/(\w+)/(\w+)' => '$1/$2/$3'`.
If you go to `http://localhost:8080/site/hello/index3` you will get "Hello guy!".

## Quick start application

The [Piko project skeletton](https://github.com/ilhooq/piko-project) is an example on how to structure a Piko based application.

1. Install via composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this project template using the following command:

```bash
composer create-project ilhooq/piko-project yourproject
```

2. Run

```bash
cd yourproject && php -S localhost:8080 -t web
```

## Inspiration

The concepts used in Piko are heavily inspired by [Yii2 framework](https://www.yiiframework.com/).


