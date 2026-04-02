<?php
namespace Piko\Tests\lib;

class TestMissingDependency
{
    public UserService $user;
    public int $id;

    public function __construct(UserService $user, int $id)
    {
        $this->user = $user;
        $this->id = $id;
    }
}
