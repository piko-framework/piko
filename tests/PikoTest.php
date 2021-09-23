<?php
use PHPUnit\Framework\TestCase;
use piko\Piko;

class PikoTest extends TestCase
{
    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDownAfterClass()
     */
    public static function tearDownAfterClass(): void
    {
        Piko::reset();
    }

    public function testIfCreateObjectProduceSingleton()
    {
        $date = Piko::createObject('DateTime');
        $date2 = Piko::createObject('DateTime');

        $this->assertEquals(spl_object_hash($date), spl_object_hash($date2));
    }

    public function testAlias()
    {
        $this->assertFalse(Piko::getAlias('@test'));
        Piko::setAlias('@tests', __DIR__);
        $this->assertEquals(__DIR__, Piko::getAlias('@tests'));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Alias must start with the @ character');
        Piko::setAlias('#test', __DIR__);
    }
}
