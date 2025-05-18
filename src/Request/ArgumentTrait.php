<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request;

trait ArgumentTrait
{
    protected(set) string $name;
    public bool $required = false;
    public ?string $description = null;
}
