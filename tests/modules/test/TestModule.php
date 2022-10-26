<?php
namespace tests\modules\test;

use Piko\Piko;

class TestModule extends \Piko\Module
{
    public $modules = [
        'sub' => 'tests\modules\test\sub\SubModule',
        'form' => 'tests\modules\test\models\ContactForm', // Not a module
    ];

    public function bootstrap()
    {
        Piko::set('TestModule::bootstrap', true);
    }
}