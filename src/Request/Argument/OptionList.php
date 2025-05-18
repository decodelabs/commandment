<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request\Argument;

use DecodeLabs\Commandment\Request\Argument;
use DecodeLabs\Commandment\Request\ArgumentTrait;

class OptionList implements Argument
{
    use ArgumentTrait;
    use ValueTrait;

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
        bool $required = false,
        ?array $default = null,
        ?array $options = null,
        ?int $min = null,
        ?int $max = null,
        ?string $description = null
    ) {
        $this->name = $name;
        $this->required = $required;
        $this->default = $default;
        $this->options = $options;
        $this->min = $min;
        $this->max = $max;
        $this->description = $description;
    }
}
