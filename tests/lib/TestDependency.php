<?php
namespace Piko\Tests\lib;

class TestDependency
{
    public \PDO $db;
    public int $id;

    public function __construct(\PDO $db, int $id)
    {
        $this->db = $db;
        $this->id = $id;
    }
}
