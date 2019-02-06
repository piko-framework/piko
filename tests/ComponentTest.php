<?php
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    public function onTestEvent2($a, $b, $c)
    {
        return $a + $b + $c;
    }

    public static function onTestEvent3($a, $b, $c)
    {
        return $a + $b + $c;
    }

    public function testEvents()
    {
        $component = new \piko\Component;

        // Test registering a Closure
        $component->on('test', function ($a, $b, $c) {
            $this->assertEquals(1, $a);
            $this->assertEquals(2, $b);
            $this->assertEquals(3, $c);
            return $a + $b + $c;
        });

        // Test registering a method
        $component->on('test', [$this, 'onTestEvent2']);

        // Test registering a static method
        $component->on('test', 'ComponentTest::onTestEvent3');

        $result = $component->trigger('test', [1, 2, 3]);

        $this->assertEquals(18, array_sum($result));

        // Test passing a parameter by reference
        $content = 'Test';

        $component->on('afterContent', function (&$data) {
            $data .= ' modified';
        });

        $component->trigger('afterContent', [&$content]);

        $this->assertEquals('Test modified', $content);
    }

    public function testBehavior()
    {
        $component = new \piko\Component;

        $component->attachBehavior('sum', function ($a, $b) {
            return $a + $b;
        });

        $this->assertEquals(12, $component->sum(10, 2));
    }
}
