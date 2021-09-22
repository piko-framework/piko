<?php
namespace tests\modules\test\sub;

class SubModule extends \piko\Module
{
    public $modules = [
        'til' => 'tests\modules\test\sub\til\SubtilModule'
    ];
}