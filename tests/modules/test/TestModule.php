<?php
namespace tests\modules\test;

class TestModule extends \piko\Module
{
    public $modules = [
        'sub' => 'tests\modules\test\sub\SubModule'
    ];
}