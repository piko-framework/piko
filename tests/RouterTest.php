<?php
namespace tests;

use PHPUnit\Framework\TestCase;

use piko\Piko;
use piko\Router;

class RouterTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $router = new Router([
            'routes' => [
                '^/$' => 'test/test/index',
                '^/user/(\d+)' => 'user/default/view|id=$1',
                '^/portfolio/(\w+)/(\d+)' => 'portfolio/default/view|alias=$1&category=$2',
                '^/page-1' => 'page/default/view|alias=page-1',
                '^/page-2' => 'page/default/view|alias=page-2',
                '^/([\w-]+)$' => 'page/default/view|alias=$1',
                '^/api/even' => 'api/event/index',
                '^/admin/(\w+)/(\w+)/(\d+)' => '$1/admin/$2|id=$3',
                '^/(\w+)/(\w+)/(\w+)' => '$1/$2/$3'
            ]
        ]);

        Piko::set('router', $router);
    }

    public function testResolve()
    {
        $router = Piko::get('router');

        $bases = ['', '/subdir'];

        foreach ($bases as $base) {
            Piko::setAlias('@web', $base);
            $_SERVER['REQUEST_URI'] = $base . '/';
            $this->assertEquals('test/test/index', $router->resolve());

            $_SERVER['REQUEST_URI'] = $base . '/user/10';
            $this->assertEquals('user/default/view', $router->resolve());
            $this->assertEquals(10, $_GET['id']);

            $_SERVER['REQUEST_URI'] = $base . '/portfolio/toto/5';
            $this->assertEquals('portfolio/default/view', $router->resolve());
            $this->assertEquals(5, $_GET['category']);
            $this->assertEquals('toto', $_GET['alias']);

            $_SERVER['REQUEST_URI'] = $base . '/page-1';
            $this->assertEquals('page/default/view', $router->resolve());
            $this->assertEquals('page-1', $_GET['alias']);

            $_SERVER['REQUEST_URI'] = $base . '/page-2';
            $this->assertEquals('page/default/view', $router->resolve());
            $this->assertEquals('page-2', $_GET['alias']);

            $_SERVER['REQUEST_URI'] = $base . '/page-3';
            $this->assertEquals('page/default/view', $router->resolve());
            $this->assertEquals('page-3', $_GET['alias']);

            $_SERVER['REQUEST_URI'] = $base . '/blog/default/index';
            $this->assertEquals('blog/default/index', $router->resolve());

            $_SERVER['REQUEST_URI'] = $base . '/admin/user/edit/5';
            $this->assertEquals('user/admin/edit', $router->resolve());

            $_SERVER['REQUEST_URI'] = $base . '/api/event';
            $this->assertEquals('api/event/index', $router->resolve());
        }
    }

    public function testGetUrl()
    {
        $router = Piko::get('router');

        $bases = ['', '/subdir'];

        foreach ($bases as $base) {
            Piko::setAlias('@web', $base);
            $this->assertEquals($base . '/', $router->getUrl('test/test/index'));
            $this->assertEquals($base . '/user/2',  $router->getUrl('user/default/view', ['id' => 2]));
            $this->assertEquals($base . '/portfolio/toto/5', $router->getUrl(
                'portfolio/default/view',
                ['category' => 5, 'alias' => 'toto']
            ));
            $this->assertEquals($base . '/page-1',  $router->getUrl('page/default/view', ['alias' => 'page-1']));
            $this->assertEquals($base . '/page-2',  $router->getUrl('page/default/view', ['alias' => 'page-2']));
            $this->assertEquals($base . '/page-3',  $router->getUrl('page/default/view', ['alias' => 'page-3']));
            $this->assertEquals($base . '/blog/default/index', $router->getUrl('blog/default/index'));
            $this->assertEquals($base . '/blog/default/view/?id=2', $router->getUrl('blog/default/view', ['id' => 2]));
            $this->assertEquals($base . '/admin/user/edit/5', $router->getUrl('user/admin/edit', ['id' => 5]));
        }
    }
}
