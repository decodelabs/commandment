<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request\Parameter;

use DecodeLabs\Commandment\Request\Argument;
use DecodeLabs\Commandment\Request\Parameter;
use DecodeLabs\Glitch\Dumpable;

/**
 * @implements Parameter<list<string>>
 */
class ValueList implements
    Parameter,
    Dumpable
{
    protected(set) string $name;
    public int $instances { get => 1; }

    /**
     * @var list<string>
     */
    protected(set) array $value;

    protected(set) ?Argument $argument;

    /**
     * @param list<string> $value
     */
    public function __construct(
        string $name,
        array $value,
        ?Argument $argument
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->argument = $argument;
    }

    public function addValue(
        string $value
    ): void {
        $this->value[] = $value;
    }

    public function glitchDump(): iterable
    {
        yield 'properties' => [
            'value' => $this->value
        ];
    }
}
