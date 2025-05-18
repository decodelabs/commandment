<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request\Argument;

use DecodeLabs\Commandment\Request\Argument;

class Flag implements Argument
{
    protected(set) string $name;
    protected(set) ?string $shortcut = null;

    public bool $required { get => false; }
    public bool $default { get => false; }

    public ?string $description = null;

    public function __construct(
        string $name,
        ?string $shortcut = null,
        ?string $description = null
    ) {
        $this->name = $name;
        $this->shortcut = $shortcut;
        $this->description = $description;
    }
}
