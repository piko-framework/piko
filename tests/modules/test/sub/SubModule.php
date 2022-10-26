<?php
namespace tests\modules\test\sub;

class SubModule extends \Piko\Module
{
    public $modules = [
        'til' => 'tests\modules\test\sub\til\SubtilModule'
    ];
}