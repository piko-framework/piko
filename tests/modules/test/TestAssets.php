<?php
namespace tests\modules\test;

class TestAssets extends \piko\AssetBundle
{
    public $name = 'test';

    public $sourcePath =  __DIR__ . '/assets';

    public $js = [
        'test.js',
    ];

    public $css = [
        'css/test.css',
    ];

    public $dependencies = [
        TestParentAssets::class
    ];

    public $registrationCount = 0;

    protected function init(): void
    {
        $this->on('register', function($className, $bundle) {
            if ($bundle instanceof TestAssets) {
                $bundle->registrationCount++;
            }
        });
    }
}
