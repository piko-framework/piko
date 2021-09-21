<?php
use PHPUnit\Framework\TestCase;
use piko\Piko;

class PikoTest extends TestCase
{
    public function testIfCreateObjectProduceSingleton()
    {
        $date = Piko::createObject('DateTime');
        $date2 = Piko::createObject('DateTime');

        $this->assertEquals(spl_object_hash($date), spl_object_hash($date2));
    }

    public function testAlias()
    {
        Piko::setAlias('@tests', __DIR__);
        $this->assertEquals(__FILE__, Piko::getAlias('@tests/' . basename(__FILE__)));
    }
}