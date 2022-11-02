<?php

/**
 * This file is part of Piko Framework
 *
 * @copyright 2019-2022 Sylvain Philip
 * @license LGPL-3.0-or-later; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko\Application\Event;

use Piko\Event;
use Piko\Application;

/**
 * Event after the uri built
 *
 * @author Sylvain Philip <contact@sphilip.com>
 */
class InitEvent extends Event
{
    /**
     * @var Application
     */
    public $application;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->application = $app;
    }
}
