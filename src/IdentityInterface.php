<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2021 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */
namespace piko;

/**
 * User identity interface.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
interface IdentityInterface
{
    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * null should be returned if such an identity cannot be found
     */
    public static function findIdentity($id);

    /**
     * Returns an ID that can uniquely identify a user identity.
     *
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId();
}
