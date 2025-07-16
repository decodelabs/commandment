<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Argument;

use Attribute;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\ArgumentTrait;

#[Attribute(
    Attribute::TARGET_CLASS |
    Attribute::TARGET_METHOD |
    Attribute::IS_REPEATABLE
)]
class Value implements Argument
{
    use ArgumentTrait;
    use ValueTrait;

    public protected(set) ?string $default = null;

    /**
     * @param ?list<string> $options
     */
    public function __construct(
        string $name,
        bool $required = false,
        ?string $default = null,
        ?array $options = null,
        ?string $description = null
    ) {
        $this->name = $name;
        $this->required = $required;
        $this->default = $default;
        $this->options = $options;
        $this->description = $description;
    }
}
