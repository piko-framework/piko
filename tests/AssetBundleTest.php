<?php
use PHPUnit\Framework\TestCase;

use piko\Piko;
use tests\modules\test\TestAssets;
use tests\modules\test\WrongAssets;
use piko\View;

class AssetBundleTest extends TestCase
{
    const PUBLIC_DIR = __DIR__ . '/www';

    public static function setUpBeforeClass(): void
    {
        mkdir(static::PUBLIC_DIR);
        Piko::setAlias('@webroot', static::PUBLIC_DIR);
        Piko::setAlias('@web', '');
    }

    public static function tearDownAfterClass(): void
    {
        exec('rm -rf ' . static::PUBLIC_DIR);
    }

    public function testBundle()
    {
        $view = new View();

        $bundle = TestAssets::register($view);
        $this->assertEquals(1, $bundle->registrationCount);

        // Check if bundle is registered once
        $bundle = TestAssets::register($view);
        $this->assertEquals(1, $bundle->registrationCount);

        $output = $view->render(__DIR__ . '/layouts/main.php', ['content' => '']);

        $this->assertDirectoryExists(self::PUBLIC_DIR . '/assets/test-parent');
        $this->assertDirectoryExists(self::PUBLIC_DIR . '/assets/test');
        $this->assertDirectoryExists(self::PUBLIC_DIR . '/assets/test-parent/css');
        $this->assertDirectoryExists(self::PUBLIC_DIR . '/assets/test/css');
        $this->assertFileExists(self::PUBLIC_DIR . '/assets/test-parent/css/parent.css');
        $this->assertFileExists(self::PUBLIC_DIR . '/assets/test/css/test.css');
        $this->assertFileExists(self::PUBLIC_DIR . '/assets/test-parent/parent.js');
        $this->assertFileExists(self::PUBLIC_DIR . '/assets/test/test.js');

        $this->assertMatchesRegularExpression('`<link href="/assets/test-parent/css/parent.css" rel="stylesheet">`', $output);
        $this->assertMatchesRegularExpression('`<link href="/assets/test/css/test.css" rel="stylesheet">`', $output);
        $this->assertMatchesRegularExpression('`<script src="/assets/test-parent/parent.js"></script>`', $output);
        $this->assertMatchesRegularExpression('`<script src="/assets/test/test.js"></script>`', $output);
        $this->assertMatchesRegularExpression('`<link href="http://domain.com/css/test.css" rel="stylesheet">`', $output);
        $this->assertMatchesRegularExpression('`<script src="http://domain.com/js/test.js"></script>`', $output);
    }

    public function testBundleWithWrongPath()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Src: ' . (new WrongAssets())->sourcePath . ' does not exists.');

        $view = new View();
        WrongAssets::register($view);
    }
}
