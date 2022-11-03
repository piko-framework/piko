<?php
namespace Piko\Tests\modules\test\sub;

class SubModule extends \Piko\Module
{
    public $modules = [
        'til' => 'Piko\Tests\modules\test\sub\til\SubtilModule'
    ];
}