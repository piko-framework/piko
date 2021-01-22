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
 * Miscellaneous utils.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Utils
{
    /**
     * Parse an environment configuration file and set environment variables.
     * The expected format of the configuration file is :
     * ```
     * ...
     * ENV_KEY1 = env_value1
     * ENV_KEY2 = env_value2
     * ...
     * ```
     * @param string $file The file path.
     * @return void
     * @throws \RuntimeException If file not found.
     */
    public static function parseEnvFile($file)
    {
        $handle = fopen($file, 'r');

        if (!$handle) {
            throw new \RuntimeException("Can't open file $file");
        }

        while (($line = fgets($handle)) !== false) {
            if (($pos = strpos($line, '=')) !== false) {
                $key = trim(substr($line, 0, $pos));
                $val = trim(substr($line, $pos + 1));
                putenv("{$key}={$val}");
            }
        }

        fclose($handle);
    }
}
