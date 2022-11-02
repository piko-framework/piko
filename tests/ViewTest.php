<?php
use PHPUnit\Framework\TestCase;

use Piko\View;
use Piko\View\Event\BeforeRenderEvent;
use Piko\View\Event\AfterRenderEvent;

class ViewTest extends TestCase
{
    public function testRegisterAssets()
    {
        $view = new View();
        $view->registerJs('$(function(){});');
        $view->registerJs('var test=null;', View::POS_HEAD, 'test');
        $view->registerJsFile('/js/site.js');
        $view->registerJsFile('/js/site-head.js', View::POS_HEAD, 'test');
        $view->registerCSS('body{colo:red}');
        $view->registerCSSFile('/css/site.css');

        $this->assertContains('$(function(){});', $view->js[View::POS_END]);
        $this->assertContains('/js/site.js', $view->jsFiles[View::POS_END]);
        $this->assertContains('var test=null;', $view->js[View::POS_HEAD]);
        $this->assertContains('/js/site-head.js', $view->jsFiles[View::POS_HEAD]);
        $this->assertArrayHasKey('test', $view->js[View::POS_HEAD]);
        $this->assertArrayHasKey('test', $view->jsFiles[View::POS_HEAD]);

        $output = $view->render(__DIR__ . '/layouts/main.php', [
            'content' => ''
        ]);

        $this->assertMatchesRegularExpression('#<head>.*(var test=null).*</head>#s', $output);
        $this->assertMatchesRegularExpression('#<head>.*(js/site-head\.js).*</head>#s', $output);
        $this->assertMatchesRegularExpression('#<head>.*(body{colo:red}).*</head>#s', $output);
        $this->assertMatchesRegularExpression('#<head>.*(css/site\.css).*</head>#s', $output);
        $this->assertMatchesRegularExpression('#<body>.*(\$\(function\(\){}).*</body>#s', $output);
        $this->assertMatchesRegularExpression('#<body>.*(js/site\.js).*</body>#s', $output);
    }

    public function testRegisterCustomHeadString()
    {
        $view = new View();

        $view->head[] = '<!-- HELLO HEAD -->';

        $this->assertMatchesRegularExpression(
            '#<!-- HELLO HEAD -->#',
            $view->render(__DIR__ . '/layouts/main.php', [
                'content' => ''
            ])
        );
    }

    public function testRegisterCustomFooterString()
    {
        $view = new View();

        $view->endBody[] = '<!-- HELLO FOOTER -->';

        $this->assertMatchesRegularExpression(
            '#<!-- HELLO FOOTER -->#',
            $view->render(__DIR__ . '/layouts/main.php', [
                'content' => ''
            ])
        );
    }

    public function testRenderNotExistentFile()
    {
        $view = new View();
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Cannot find the view file for the viewname: not_exist.php');
        $view->render('not_exist.php');
    }

    public function testRenderException()
    {
        $view = new View();
        $this->expectException('Exception');
        $this->expectExceptionMessage('I cannot be rendered');
        $view->render(__DIR__ . '/layouts/exception.php');
    }

    public function testViewTheme()
    {
        $view = new View([
            'themeMap' => [
                __DIR__ . '/layouts' => __DIR__ . '/theme'
            ]
        ]);

        $this->assertMatchesRegularExpression(
            '#<h1>Main layout override</h1>#',
            $view->render(__DIR__ . '/layouts/main.php', [
                'content' => ''
            ])
        );
    }

    public function testBeforeRender()
    {
        $view = new View();
        $view->on(BeforeRenderEvent::class, function(BeforeRenderEvent $event) {
            $event->file =  __DIR__ . '/theme/main.php';
            $event->model = ['content' => 'testBeforeRender'];
        });

        $output = $view->render(__DIR__ . '/layouts/main.php', [
            'content' => ''
        ]);

        $this->assertMatchesRegularExpression('#<h1>Main layout override</h1>#', $output);
        $this->assertMatchesRegularExpression('#testBeforeRender#', $output);
    }

    public function testAfterRender()
    {
        $view = new View();
        $view->on(AfterRenderEvent::class, function(AfterRenderEvent $event) {
            $event->output .= '<!-- test afterRender -->';
        });

        $output = $view->render(__DIR__ . '/layouts/main.php', [
            'content' => ''
        ]);

        $this->assertMatchesRegularExpression('#<!-- test afterRender -->#', $output);
    }
}
