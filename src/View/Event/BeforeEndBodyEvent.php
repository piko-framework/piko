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
 * Event dispatched before endBody rendering
 *
 * @author Sylvain Philip <contact@sphilip.com>
 */
class BeforeEndBodyEvent extends Event
{
    /**
     * The output before endBody
     *
     * @var string
     * @see \Piko\View::endBody() method
     */
    public $output = '';
}
