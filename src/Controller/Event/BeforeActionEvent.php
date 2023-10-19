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

/**
 * BeforeActionEvent is an event triggered before to call action method
 *
 * @author Sylvain Philip <contact@sphilip.com>
 */
class BeforeActionEvent extends Event
{
    /**
     * @var Controller
     */
    public $controller;

    /**
     * The action id
     *
     * @var string
     */
    public $actionId;

    /**
     * The action parameters
     *
     * @var array<string, mixed>
     */
    public $params = [];

    /**
     * @param Controller $controller A controller instance
     * @param array<string, mixed> $params The action parameters
     */
    public function __construct(Controller $controller, string $actionId, array $params = [])
    {
        $this->controller = $controller;
        $this->actionId = $actionId;
        $this->params = $params;
    }
}
