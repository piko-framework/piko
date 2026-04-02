<?php
namespace Piko\Tests\lib;

class TestOptionalVariadic
{
    /**
     * @var array<mixed>
     */
    public array $args;

    public function __construct(...$args)
    {
        $this->args = $args;
    }
}
