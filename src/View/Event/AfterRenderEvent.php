<?php

/**
 * This file is part of Piko Framework
 *
 * @copyright 2019-2022 Sylvain Philip
 * @license LGPL-3.0-or-later; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

declare(strict_types=1);

namespace Piko\View\Event;

use Piko\Event;

/**
 * BeforeRenderEvent is an event triggered after view script rendering
 *
 * @author Sylvain Philip <contact@sphilip.com>
 */
class AfterRenderEvent extends Event
{
    /**
     * @var string
     */
    public $output = '';

    /**
     * @param string $output The view script $output
     */
    public function __construct(string $output)
    {
        $this->output = $output;
    }
}
