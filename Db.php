<?php
/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/ilhooq/piko
 */
declare(strict_types=1);

namespace piko;

use PDO;

/**
 * Db is the base class to access SQL databases. It's just a proxy to \PDO.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Db extends PDO
{
    /**
     * Extends PDO constructor to accept an array of configuration.
     *
     * @param array $config An array (name-value pairs) containing
     * dsn, username, password and options of the database.
     * @see PDO::__construct()
     */
    public function __construct($config = [])
    {
        $dsn      = isset($config['dsn'])      ? $config['dsn']      : null;
        $username = isset($config['username']) ? $config['username'] : null;
        $password = isset($config['password']) ? $config['password'] : null;
        $options  = isset($config['options'])  ? $config['options']  : null;

        parent::__construct($dsn, $username, $password, $options);
    }
}
