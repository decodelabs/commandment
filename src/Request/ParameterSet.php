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
use DecodeLabs\Glitch\Dumpable;

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

    public function getAsBool(
        string $name
    ): bool {
        if(!$parameter = ($this->parameters[$name] ?? null)) {
            return false;
        }

        if($parameter instanceof FlagParameter) {
            return true;
        }

        if($parameter instanceof ValueParameter) {
            return (bool)Coercion::parseBool($parameter->value);
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

    public function getAsString(
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

    public function getAsInt(
        string $name
    ): ?int {
        if(null !== ($output = $this->getAsString($name))) {
            $output = Coercion::asInt($output);
        }

        return $output;
    }

    /**
     * @return ?list<string>
     */
    public function getAsStringList(
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
     * @return ?list<int>
     */
    public function getAsIntList(
        string $name
    ): ?array {
        if(null !== ($output = $this->getAsStringList($name))) {
            $output = array_map(
                static fn($value) => Coercion::asInt($value),
                $output
            );
        }

        return $output;
    }



    public function glitchDump(): iterable
    {
        yield 'values' => $this->parameters;
    }
}
