<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment;

interface Middleware
{
    public int $priority { get; }

    public function handle(
        Request $request
    ): Request;
}
