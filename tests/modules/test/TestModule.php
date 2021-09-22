<?php
namespace tests\modules\test;

use piko\Piko;

class TestModule extends \piko\Module
{
    public $modules = [
        'sub' => 'tests\modules\test\sub\SubModule'
    ];

    public function bootstrap()
    {
        Piko::set('TestModule::bootstrap', true);
    }
}