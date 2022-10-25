<?php

/**
 * This file is part of Piko - Web micro framework
 *
 * @copyright 2019-2021 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace piko;

use Exception;
use Throwable;

/**
 * HttpException convert exception code to http status header.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 * @see \Exception
 */
class HttpException extends Exception
{
    /**
     * Constructor set http header with response code and message if code is given
     *
     * @param int $code The exception code (should be an HTTP status code, eg. 404)
     * @param string $message The exception message.
     * @param Throwable $previous A previous exception.
     */
    public function __construct(int $code = 404, string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
