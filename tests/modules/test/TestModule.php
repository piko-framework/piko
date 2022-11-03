<?php
namespace Piko\Tests\modules\test;

class TestModule extends \Piko\Module
{
    public $modules = [
        'sub' => 'Piko\Tests\modules\test\sub\SubModule',
        'form' => 'Piko\Tests\modules\test\models\ContactForm', // Not a module
    ];

    public function bootstrap()
    {
        $this->application->language = 'fr';
    }
}