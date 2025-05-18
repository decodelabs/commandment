<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment;

interface Action
{
    public function execute(
        Request $request
    ): bool;
}
