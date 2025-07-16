<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request\Parameter;

use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request\Parameter;
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;

/**
 * @implements Parameter<list<string>>
 */
class ValueList implements
    Parameter,
    Dumpable
{
    public protected(set) string $name;
    public int $instances { get => 1; }

    /**
     * @var list<string>
     */
    public protected(set) array $value;

    public protected(set) ?Argument $argument;

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

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->itemName = $this->name;
        $entity->setProperty('value', $this->value, readOnly: true);
        return $entity;
    }
}
