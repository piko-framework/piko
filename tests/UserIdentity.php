<?php
namespace tests;

class UserIdentity implements \piko\IdentityInterface
{
    private static $users = [
        1 => 'sylvain',
        2 => 'pierre',
        3 => 'paul'
    ];

    public $id;
    public $username;

    public static function findIdentity($id)
    {
        if (isset(static::$users[$id])) {
            $user = new static();
            $user->id = $id;
            $user->username = static::$users[$id];

            return $user;
        }

        return null;
    }

    public function getId()
    {
        return $this->id;
    }
}
