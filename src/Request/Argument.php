<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request;

interface Argument
{
    public string $name { get; }
    public ?string $description { get; }
    public bool $required { get; }
    public mixed $default { get; }
}
