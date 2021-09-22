<?php
use PHPUnit\Framework\TestCase;

use tests\modules\test\TestModule;
use tests\modules\test\sub\SubModule;
use tests\modules\test\sub\til\SubtilModule;

class ModuleTest extends TestCase
{
    public function testGetModule()
    {
        $module = new TestModule();
        $subModule = $module->getModule('sub');
        $this->assertInstanceOf(SubModule::class, $subModule);
        $subtilModule = $subModule->getModule('til');
        $this->assertInstanceOf(SubtilModule::class, $subtilModule);
    }
}
