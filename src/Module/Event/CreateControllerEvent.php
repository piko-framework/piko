<?php

/**
 * This file is part of Piko Framework
 *
 * @copyright 2019-2022 Sylvain Philip
 * @license LGPL-3.0-or-later; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko\Module\Event;

use Piko\Event;
use Piko\Controller;

/**
 * Event corresponding to the creation of a controller
 *
 * @author Sylvain Philip <contact@sphilip.com>
 */
class CreateControllerEvent extends Event
{
    /**
     * @var Controller
     */
    public $controller;

    /**
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }
}
