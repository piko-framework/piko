<?php
use PHPUnit\Framework\TestCase;
use piko\Piko;
use piko\Utils;

class PikoTest extends TestCase
{
    public function testIfCreateObjectProduceSingleton()
    {
        $date = Piko::createObject('DateTime');
        $date2 = Piko::createObject('DateTime');

        $this->assertEquals(spl_object_hash($date), spl_object_hash($date2));
    }

    public function testEnvFile()
    {
        $data = "\t  APP_LANGUAGE  =      fr\r\n";
        $data .= "APP_EMAIL      =      \n";
        $data .= "ENV      =   dev   \n";

        Utils::parseEnvFile('data:text/plain;base64,' . base64_encode($data));

        $this->assertEquals('fr', getenv('APP_LANGUAGE'));
        $this->assertEmpty(getenv('APP_EMAIL'));
        $this->assertEquals('dev', getenv('ENV'));
    }

    public function testAlias()
    {
        Piko::setAlias('@tests', __DIR__);
        $this->assertEquals(__FILE__, Piko::getAlias('@tests/' . basename(__FILE__)));
    }
}