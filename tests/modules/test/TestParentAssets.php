<?php
namespace tests\modules\test;

class TestParentAssets extends \piko\AssetBundle
{
    public $name = 'test-parent';

    public $sourcePath =  __DIR__ . '/assets';

    public $js = [
        'parent.js',
        'http://domain.com/js/test.js'
    ];

    public $css = [
        'css/parent.css',
        'http://domain.com/css/test.css'
    ];
}
