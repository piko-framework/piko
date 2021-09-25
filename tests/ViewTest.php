<?php
use PHPUnit\Framework\TestCase;

use piko\View;
use piko\Application;

class ViewTest extends TestCase
{
    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        new Application([]);
    }


    public function testRegisterAssets()
    {
        $view = Application::getInstance()->getView();
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
    }

    /**
     * @depends testRegisterAssets
     */
    public function testRegisterCustomHeadString()
    {
        $view = Application::getInstance()->getView();

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
        $view = Application::getInstance()->getView();

        $view->endBody[] = '<!-- HELLO FOOTER -->';

        $this->assertMatchesRegularExpression(
            '#<!-- HELLO FOOTER -->#',
            $view->render(__DIR__ . '/layouts/main.php', [
                'content' => ''
            ])
        );
    }

    public function testGetUrl()
    {
        $view = Application::getInstance()->getView();
        $this->assertEquals('/site/index/index', $view->getUrl('site/index/index'));
    }

    public function testRenderNotExistentFile()
    {
        $view = Application::getInstance()->getView();

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Cannot find the view file for the viewname: not_exist.php');

        $view->render('not_exist.php');
    }

    public function testRenderException()
    {
        $view = Application::getInstance()->getView();

        $this->expectException('Exception');
        $this->expectExceptionMessage('I cannot be rendered');

        $view->render(__DIR__ . '/layouts/exception.php');
    }

    public function testViewTheme()
    {
        $view = Application::getInstance()->getView();
        $view->themeMap = [
            __DIR__ . '/layouts' => __DIR__ . '/theme'
        ];

        $this->assertMatchesRegularExpression(
            '#<h1>Main layout override</h1>#',
            $view->render(__DIR__ . '/layouts/main.php', [
                'content' => ''
            ])
        );
    }
}
