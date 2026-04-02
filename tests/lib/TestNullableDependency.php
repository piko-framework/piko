<?php
namespace Piko\Tests\lib;

class TestNullableDependency
{
    public ?UserService $user;

    public function __construct(?UserService $user)
    {
        $this->user = $user;
    }
}
