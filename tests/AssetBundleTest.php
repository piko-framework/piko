<?php
use PHPUnit\Framework\TestCase;

use piko\Piko;
use piko\AssetBundle;
use piko\View;

class TestBundle extends AssetBundle
{
    public $name = 'test';
    public $sourcePath =  __DIR__ . '/testBundle/sources';
    public $js = ['test.js', 'http://domain.com/js/test.js'];
    public $css = ['test.css', 'http://domain.com/css/test.css'];
}

class AssetBundleTest extends TestCase
{
    protected $testDir = __DIR__ . '/testBundle';

    protected function setUp(): void
    {
        mkdir($this->testDir);
        mkdir($this->testDir . '/sources');
        mkdir($this->testDir . '/public');
        file_put_contents($this->testDir . '/sources/test.css', '');
        file_put_contents($this->testDir . '/sources/test.js', '');
        file_put_contents($this->testDir . '/test.php', '<?php echo $this->head(); echo $this->endBody(); ?>');
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . $this->testDir);
    }

    public function testBundle()
    {
        Piko::setAlias('@webroot', $this->testDir . '/public');
        Piko::setAlias('@web', '');
        $view = new View;
        TestBundle::register($view);
        $output = $view->render($this->testDir . '/test.php');

        $this->assertDirectoryExists($this->testDir . '/public/assets/test');
        $this->assertFileExists($this->testDir . '/public/assets/test/test.css');
        $this->assertFileExists($this->testDir . '/public/assets/test/test.js');

        $this->assertMatchesRegularExpression('`<link href="/assets/test/test.css" rel="stylesheet">`', $output);
        $this->assertMatchesRegularExpression('`<script src="/assets/test/test.js"></script>`', $output);
        $this->assertMatchesRegularExpression('`<link href="http://domain.com/css/test.css" rel="stylesheet">`', $output);
        $this->assertMatchesRegularExpression('`<script src="http://domain.com/js/test.js"></script>`', $output);
    }
}