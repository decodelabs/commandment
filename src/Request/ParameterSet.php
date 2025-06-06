<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request;

use DecodeLabs\Coercion;
use DecodeLabs\Commandment\Request\Parameter\Flag as FlagParameter;
use DecodeLabs\Commandment\Request\Parameter\Value as ValueParameter;
use DecodeLabs\Commandment\Request\Parameter\ValueList as ValueListParameter;
use DecodeLabs\Exceptional;
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;

class ParameterSet implements Dumpable
{
    /**
     * @var array<string,Parameter<string|list<string>>>
     */
    protected(set) array $parameters = [];

    /**
     * @param array<string,Parameter<string|list<string>>> $parameters
     */
    public function __construct(
        array $parameters = []
    ) {
        $this->parameters = $parameters;
    }

    public function has(
        string $name
    ): bool {
        return isset($this->parameters[$name]);
    }

    /**
     * @return Parameter<string|list<string>>
     */
    public function get(
        string $name
    ): ?Parameter {
        return $this->parameters[$name] ?? null;
    }

    public function tryBool(
        string $name
    ): ?bool {
        if(null === ($parameter = ($this->parameters[$name] ?? null))) {
            return null;
        }

        if($parameter instanceof FlagParameter) {
            return true;
        }

        if($parameter instanceof ValueParameter) {
            return Coercion::parseBool($parameter->value);
        }

        if($parameter instanceof ValueListParameter) {
            foreach($parameter->value as $value) {
                if(Coercion::parseBool($value)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function asBool(
        string $name
    ): bool {
        return (bool)$this->tryBool($name);
    }



    public function tryString(
        string $name
    ): ?string {
        if(!$parameter = ($this->parameters[$name] ?? null)) {
            return null;
        }

        if($parameter instanceof ValueParameter) {
            return $parameter->value;
        }

        if($parameter instanceof ValueListParameter) {
            return $parameter->value[0] ?? null;
        }

        return null;
    }

    public function asString(
        string $name
    ): string {
        if(null === ($output = $this->tryString($name))) {
            throw Exceptional::InvalidArgument(
                'Parameter "' . $name . '" does not exist or does not have a value'
            );
        }

        return $output;
    }



    public function tryInt(
        string $name
    ): ?int {
        if(null !== ($output = $this->tryString($name))) {
            $output = Coercion::asInt($output);
        }

        return $output;
    }

    public function asInt(
        string $name
    ): ?int {
        return Coercion::asInt($this->asString($name));
    }



    /**
     * @return ?list<string>
     */
    public function tryStringList(
        string $name
    ): ?array {
        if(!$parameter = ($this->parameters[$name] ?? null)) {
            return null;
        }

        if($parameter instanceof ValueListParameter) {
            return $parameter->value;
        }

        if($parameter instanceof ValueParameter) {
            return [$parameter->value];
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function asStringList(
        string $name
    ): array {
        if(null === ($output = $this->tryStringList($name))) {
            $output = [];
        }

        return $output;
    }



    /**
     * @return ?list<int>
     */
    public function tryIntList(
        string $name
    ): ?array {
        if(null !== ($output = $this->tryStringList($name))) {
            $output = array_map(
                static fn($value) => Coercion::asInt($value),
                $output
            );
        }

        return $output;
    }

    /**
     * @return list<int>
     */
    public function asIntList(
        string $name
    ): array {
        if(null === ($output = $this->tryIntList($name))) {
            $output = [];
        }

        return $output;
    }


    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->values = $this->parameters;
        return $entity;
    }
}
