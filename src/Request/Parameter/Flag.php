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
 * @implements Parameter<bool>
 */
class Flag implements
    Parameter,
    Dumpable
{
    protected(set) string $name;
    protected(set) int $instances = 1;
    public bool $value { get => true; }
    protected(set) ?Argument $argument;

    public function __construct(
        string $name,
        ?Argument $argument
    ) {
        $this->name = $name;
        $this->argument = $argument;
    }

    public function incrementInstances(): void {
        $this->instances++;
    }

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->itemName = $this->name;
        $entity->setProperty('instances', $this->instances, readOnly: true);
        return $entity;
    }
}
