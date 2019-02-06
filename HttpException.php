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
 * HttpException convert exception code to http status header.
 * @author Sylvain PHILIP <contact@sphilip.com>
 * @see \Exception
 */
class HttpException extends \Exception
{
    /**
     * Constructor sends http header if php SAPI != cli.
     * @param string $message The exception message.
     * @param int $code The exception code (should be an HTTP status code, eg. 404)
     * @param \Throwable $previous A previous exception.
     */
    public function __construct($message = null, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if ($e->getCode() && php_sapi_name() != 'cli') {
            $protocol = isset($_SERVER['SERVER_PROTOCOL'])? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
            header($protocol . ' ' . $e->getCode() . ' ' . $e->getMessage());
        }
    }
}
