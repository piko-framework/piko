<?php

/**
 * This file is part of Piko Framework
 *
 * @copyright 2019-2022 Sylvain Philip
 * @license LGPL-3.0-or-later; see LICENSE.txt
 * @link https://github.com/piko-framework/piko
 */

namespace Piko\View;

/**
 * View interface.
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
interface ViewInterface
{
    /**
     * Render the view.
     *
     * @param string $file The view file name.
     * @param array<mixed> $model An array of data (name-value pairs) to transmit to the view.
     * @return string The view's output.
     */
    public function render(string $file, array $model = []): string;
}
