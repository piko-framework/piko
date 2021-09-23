<?php
use PHPUnit\Framework\TestCase;
use tests\modules\test\TestComponent;

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
        $component = new TestComponent();

        // Test registering a Closure
        $component->on('test', function ($a, $b, $c) {
            $this->assertEquals(1, $a);
            $this->assertEquals(2, $b);
            $this->assertEquals(3, $c);
            return $a + $b + $c;
        });

        TestComponent::when('test', function ($a, $b, $c) {
            $this->assertEquals(1, $a);
            $this->assertEquals(2, $b);
            $this->assertEquals(3, $c);
            return $a + $b + $c;
        });

        // Test registering a method
        TestComponent::when('test', [$this, 'onTestEvent2'], 'before');

        // Test registering a static method
        $component->on('test', 'ComponentTest::onTestEvent3', 'before');

        $result = $component->trigger('test', [1, 2, 3]);

        $this->assertEquals(24, array_sum($result));

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
        $component = new TestComponent;

        $component->attachBehavior('sum', function ($a, $b) {
            return $a + $b;
        });

        $this->assertEquals(12, $component->sum(10, 2));

        $component->detachBehavior('sum');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Behavior sum not registered.');

        $component->sum(10, 2);
    }
}
