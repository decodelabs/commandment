<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request\Parameter;

use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request\Parameter;
use DecodeLabs\Glitch\Dumpable;

/**
 * @implements Parameter<string>
 */
class Value implements
    Parameter,
    Dumpable
{
    protected(set) string $name;
    public int $instances { get => 1; }
    protected(set) string $value;
    protected(set) ?Argument $argument;

    public function __construct(
        string $name,
        string $value,
        ?Argument $argument
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->argument = $argument;
    }

    public function replaceValue(
        string $value
    ): void {
        $this->value = $value;
    }

    public function glitchDump(): iterable
    {
        yield 'properties' => [
            'value' => $this->value
        ];
    }
}
