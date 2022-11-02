<?php

/**
 * This file is part of Piko Framework
 *
 * @copyright 2019-2022 Sylvain Philip
 * @license LGPL-3.0-or-later; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko\Controller\Event;

use Piko\Controller;
use Piko\Event;
use Psr\Http\Message\ResponseInterface;

/**
 * AfterActionEvent is an event triggered after action method was called
 *
 * @author Sylvain Philip <contact@sphilip.com>
 */
class AfterActionEvent extends Event
{
    /**
     * @var Controller
     */
    public $controller;

    /**
     * The action method response
     *
     * @var ResponseInterface
     */
    public $response;

    /**
     * @param Controller $controller A controller instance
     * @param ResponseInterface $response The action method response
     */
    public function __construct(Controller $controller, ResponseInterface $response)
    {
        $this->controller = $controller;
        $this->response = $response;
    }
}
