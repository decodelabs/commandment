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

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class OptionList implements Argument
{
    use ArgumentTrait;
    use ValueTrait;

    protected(set) ?string $shortcut = null;

    public ?int $min = null;
    public ?int $max = null;

    /**
     * @var ?list<string>
     */
    protected(set) ?array $default = null;

    /**
     * @param ?list<string> $default
     * @param ?list<string> $options
     */
    public function __construct(
        string $name,
        ?string $shortcut = null,
        bool $required = false,
        ?array $default = null,
        ?array $options = null,
        ?int $min = null,
        ?int $max = null,
        ?string $description = null
    ) {
        $this->name = $name;
        $this->shortcut = $shortcut;
        $this->required = $required;
        $this->default = $default;
        $this->options = $options;
        $this->min = $min;
        $this->max = $max;
        $this->description = $description;
    }
}
