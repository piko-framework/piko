<?php
namespace tests;

use PHPUnit\Framework\TestCase;

use piko\Piko;
use piko\User;

class UserTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $user = new User([
            'identityClass' => '\\tests\UserIdentity',
            'accessCheckerClass' => '\\tests\AccessChecker',
        ]);

        Piko::set('user', $user);
    }

    public function testLogin()
    {
        $identity = UserIdentity::findIdentity(1);

        $this->assertEquals('sylvain', $identity->username);

        $user = Piko::get('user');
        /* @var \piko\User $user */
        @$user->login($identity);
        $this->assertFalse($user->isGuest());

        session_write_close();
    }

    public function testAlreadyConnected()
    {
        $user = Piko::get('user');

        $this->assertFalse(@$user->isGuest());

        $user = new User([
            'identityClass' => '\\tests\UserIdentity'
        ]);

        $this->assertFalse($user->isGuest());

        $this->assertEquals(1, $user->getId());

        session_write_close();
    }

    public function testPermissions()
    {
        $user = Piko::get('user');

        $this->assertFalse(@$user->can('post'));
        $this->assertTrue(@$user->can('test'));
    }

    public function testLogout()
    {
        $user = Piko::get('user');
        $this->assertFalse($user->isGuest());
        @$user->logout();

        $user = Piko::get('user');
        $this->assertTrue(@$user->isGuest());
    }
}

class AccessChecker
{
    public function checkAccess($userId, $permission)
    {
        return ($userId == 1 && $permission == 'test')? true : false;
    }
}
