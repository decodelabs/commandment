<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Argument;

use Attribute;
use DecodeLabs\Commandment\Argument;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
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
