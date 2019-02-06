# Piko

Piko is a Web micro Framework inspired by Yii2 Framework to build MVC application.

## Installation via composer

```bash
composer require ilhooq/piko
```

## Getting started

A trivial exemple in index.php :

```php
<?php
use piko\Application;

require('vendor/autoload.php');

class SiteModule extends \piko\Module
{

}

class HelloController extends \piko\Controller
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

To have a more advanced usage, take a look on the basic [Piko project skeletton](https://github.com/ilhooq/piko-project).


