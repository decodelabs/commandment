<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request\Argument;

use DecodeLabs\Commandment\Request\Argument;
use DecodeLabs\Commandment\Request\ArgumentTrait;

class Option implements Argument
{
    use ArgumentTrait;
    use ValueTrait;

    protected(set) ?string $default = null;

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
