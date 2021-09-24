<?php
use PHPUnit\Framework\TestCase;
use piko\Piko;
use piko\User;
use tests\modules\test\models\User as UserIdentity;
use tests\modules\test\AccessChecker;

class UserTest extends TestCase
{
    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDownAfterClass()
     */
    public static function tearDownAfterClass(): void
    {
        Piko::reset();
    }

    public function testLogin()
    {
        $user = new User([
            'identityClass' => UserIdentity::class,
            'accessCheckerClass' => AccessChecker::class,
        ]);

        Piko::set('user', $user);

        $identity = UserIdentity::findIdentity(1);
        $user->login($identity);
        $this->assertFalse($user->isGuest());
        $this->assertEquals(1, $user->getId());
    }

    /**
     * @depends testLogin
     */
    public function testRetrieveIdentityFromSession()
    {
        $user = new User([
            'identityClass' => UserIdentity::class,
        ]);

        $this->assertEquals('sylvain', $user->getIdentity()->username);
    }

    /**
     * @depends testLogin
     */
    public function testPermissionsAfterLogin()
    {
        /* @var $user \piko\User */
        $user = Piko::get('user');

        $this->assertFalse($user->isGuest());

        $this->assertFalse($user->can('post'));
        $this->assertTrue($user->can('test'));
    }

    /**
     * @depends testLogin
     */
    public function testPermissionsWithoutAccessChecker()
    {
        $user = new User([
            'identityClass' => UserIdentity::class,
        ]);

        $this->assertFalse($user->can('test'));
    }

    /**
     * @depends testLogin
     */
    public function testLogout()
    {
        /* @var $user \piko\User */
        $user = Piko::get('user');

        $this->assertFalse($user->isGuest());
        $user->logout();

        $this->assertTrue($user->isGuest());
    }

    public function testPermissionsBeforeAndAfterLogout()
    {
        $user = new User([
            'identityClass' => UserIdentity::class,
            'accessCheckerClass' => AccessChecker::class,
        ]);

        $identity = UserIdentity::findIdentity(1);
        $user->login($identity);

        $this->assertFalse($user->can('post'));
        $this->assertTrue($user->can('test'));
        $user->logout();
        $this->assertFalse($user->can('post'));
        $this->assertFalse($user->can('test'));
    }
}
