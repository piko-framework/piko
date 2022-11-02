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
use Piko\View;

/**
 * BeforeRenderEvent is an event triggered before view script rendering
 *
 * @author Sylvain Philip <contact@sphilip.com>
 */
class BeforeRenderEvent extends Event
{
    /**
     * @var View
     */
    public $view;

    /**
     * The view script path
     *
     * @var string
     */
    public $file = '';

    /**
     * The view script model
     *
     * @var array<string, mixed>
     */
    public $model = [];

    /**
     * @param View $view A view instance
     * @param string $file A view script path
     * @param array<string, mixed> $model The view script model
     */
    public function __construct(View $view, string $file, array $model = [])
    {
        $this->view = $view;
        $this->file = $file;
        $this->model = $model;
    }
}
