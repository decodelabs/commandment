<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request;

use DecodeLabs\Commandment\Request\Argument;

/**
 * @template T
 */
interface Parameter
{
    public string $name { get; }
    public int $instances { get; }

    /**
     * @var T
     */
    public mixed $value { get; }

    public ?Argument $argument { get; }
}
