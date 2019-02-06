<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/ilhooq/piko
 */
namespace piko;

/**
 * Application User base class.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class User extends Component
{
    /**
     * @var string the class name of the identity object.
     */
    public $identityClass;

    /**
     * @var integer the number of seconds in which the user will be logged out automatically if he
     * remains inactive.
     */
    public $authTimeout;

    /**
     * @var string The access checker to use for checking access.
     */
    public $accessCheckerClass;

    /**
     * @var IdentityInterface The identity instance.
     */
    protected $identity;

    /**
     * @var object Access checker instance.
     */
    protected $accessChecker;

    /**
     * @var array internal cache of access permissions.
     */
    protected $access = [];

    /**
     * {@inheritDoc}
     * @see \piko\Component::init()
     */
    protected function init()
    {
        if (!empty($this->authTimeout)) {
            ini_set('session.gc_maxlifetime', $this->authTimeout);
        }

        if (!empty($this->accessCheckerClass)) {
            $this->accessChecker = Piko::createObject($this->accessCheckerClass);
        }
    }

    /**
     * Get user identity
     * @return IdentityInterface|null The user identity or null if no identity is found.
     */
    public function getIdentity()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if ($this->identity === null && isset($_SESSION['_id'])) {
            $class = $this->identityClass;
            $this->identity = $class::findIdentity($_SESSION['_id']);
        }

        return $this->identity;
    }

    /**
     * Get user identifier.
     * @return NULL|string|number
     */
    public function getId()
    {
        $identity = $this->getIdentity();

        return $identity !== null ? $identity->getId() : null;
    }

    /**
     * Set user identity.
     * @param IdentityInterface $identity The user identity.
     * @throws \RuntimeException If identiy doesn't implement IdentityInterface.
     */
    public function setIdentity($identity)
    {
        if (!$identity instanceof IdentityInterface) {
            throw new \RuntimeException('The identity instance must implement IdentityInterface');
        }

        $this->identity = $identity;
        $this->access = [];
    }

    /**
     * Start the session and set user identity.
     * @param IdentityInterface $identity The user identity.
     */
    public function login($identity)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->setIdentity($identity);

        $_SESSION['_id'] = $identity->getId();
    }

    /**
     * Destroy the session and remove user identity.
     */
    public function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_destroy();

        $this->identity = null;
        $this->access = [];
    }

    /**
     * Returns a value indicating whether the user is a guest (not authenticated).
     * @return boolean whether the current user is a guest.
     */
    public function isGuest()
    {
        return $this->getIdentity() === null;
    }

    /**
     * Check if the user can do an action.
     * @param string $permission The permission name.
     * @return boolean
     */
    public function can($permission)
    {
        if (isset($this->access[$permission])) {
            return $this->access[$permission];
        }

        if ($this->accessChecker == null) {
            return false;
        }

        $access = $this->accessChecker->checkAccess($this->getId(), $permission);

        $this->access[$permission] = $access;

        return $access;
    }
}
