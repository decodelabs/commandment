<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment;

use DecodeLabs\Coercion;
use DecodeLabs\Commandment\Argument\Flag as FlagArgument;
use DecodeLabs\Commandment\Argument\Option as OptionArgument;
use DecodeLabs\Commandment\Argument\OptionList as OptionListArgument;
use DecodeLabs\Commandment\Argument\Value as ValueArgument;
use DecodeLabs\Commandment\Argument\ValueList as ValueListArgument;
use DecodeLabs\Commandment\Request\Fragment;
use DecodeLabs\Commandment\Request\Parameter\Flag as FlagParameter;
use DecodeLabs\Commandment\Request\Parameter\Value as ValueParameter;
use DecodeLabs\Commandment\Request\Parameter\ValueList as ValueListParameter;
use DecodeLabs\Commandment\Request\ParameterSet;
use DecodeLabs\Exceptional;
use DecodeLabs\Slingshot;

class Request
{
    public ParameterSet $parameters {
        get {
            return $this->parameters ??= $this->parse();
        }
    }

    public protected(set) string $command;

    /**
     * @var array<int,Fragment>
     */
    public protected(set) array $fragments = [];

    /**
     * @var array<string,Argument>
     */
    public protected(set) array $arguments = [];

    /**
     * @var array<string,mixed>
     */
    public protected(set) array $attributes = [];

    /**
     * @var array<string,mixed>
     */
    public protected(set) array $server = [];

    public protected(set) Slingshot $slingshot;

    /**
     * @param list<string|Fragment> $fragments
     * @param array<string,mixed> $attributes
     * @param array<string,mixed> $server
     */
    public function __construct(
        string $command,
        array $fragments,
        array $attributes = [],
        ?array $server = null,
        ?Slingshot $slingshot = null
    ) {
        $this->command = $command;

        foreach ($fragments as $fragment) {
            if (is_string($fragment)) {
                $this->fragments[] = new Fragment($fragment);
            } elseif ($fragment instanceof Fragment) {
                $this->fragments[] = $fragment;
            }
        }

        $this->attributes = $attributes;
        $this->server = $server ?? $_SERVER;
        $this->slingshot = $slingshot ? clone $slingshot : new Slingshot();
    }

    /**
     * @return array<string,mixed>
     */
    public function getServerParams(): array
    {
        return $this->server;
    }

    public function getServerParam(
        string $key
    ): mixed {
        return $this->server[$key] ?? null;
    }

    public function hasServerParam(
        string $key
    ): bool {
        return isset($this->server[$key]);
    }


    /**
     * @return array<string,mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }


    public function withAttribute(
        string $name,
        mixed $value
    ): static {
        $output = clone $this;
        $output->attributes[$name] = $value;

        return $output;
    }

    public function getAttribute(
        string $name,
        mixed $default = null
    ): mixed {
        if (!array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    public function withoutAttribute(
        string $name
    ): static {
        $output = clone $this;
        unset($output->attributes[$name]);

        return $output;
    }


    /**
     * @param ?list<string> $options
     */
    public function withValueArgument(
        string $name,
        bool $required = true,
        ?string $default = null,
        ?array $options = null,
        ?string $description = null,
    ): static {
        return $this->withArgument(
            new ValueArgument(
                name: $name,
                required: $required,
                default: $default,
                options: $options,
                description: $description,
            )
        );
    }

    /**
     * @param ?list<string> $default
     * @param ?list<string> $options
     */
    public function withValueListArgument(
        string $name,
        bool $required = true,
        ?array $default = null,
        ?array $options = null,
        ?int $min = null,
        ?int $max = null,
        ?string $description = null,
    ): static {
        return $this->withArgument(
            new ValueListArgument(
                name: $name,
                required: $required,
                default: $default,
                options: $options,
                min: $min,
                max: $max,
                description: $description,
            )
        );
    }

    public function withFlagArgument(
        string $name,
        ?string $shortcut = null,
        ?string $description = null,
    ): static {
        return $this->withArgument(
            new FlagArgument(
                name: $name,
                shortcut: $shortcut,
                description: $description,
            )
        );
    }

    /**
     * @param ?list<string> $options
     */
    public function withOptionArgument(
        string $name,
        ?string $shortcut = null,
        bool $required = true,
        ?string $default = null,
        ?array $options = null,
        ?string $description = null,
    ): static {
        return $this->withArgument(
            new OptionArgument(
                name: $name,
                shortcut: $shortcut,
                required: $required,
                default: $default,
                options: $options,
                description: $description,
            )
        );
    }

    /**
     * @param ?list<string> $default
     * @param ?list<string> $options
     */
    public function withOptionListArgument(
        string $name,
        ?string $shortcut = null,
        bool $required = true,
        ?array $default = null,
        ?array $options = null,
        ?int $min = null,
        ?int $max = null,
        ?string $description = null,
    ): static {
        return $this->withArgument(
            new OptionListArgument(
                name: $name,
                shortcut: $shortcut,
                required: $required,
                default: $default,
                options: $options,
                min: $min,
                max: $max,
                description: $description,
            )
        );
    }


    public function withArgument(
        Argument $argument
    ): static {
        $output = clone $this;
        $output->arguments[$argument->name] = $argument;
        return $output;
    }


    /**
     * @param list<string|Fragment> $fragments
     */
    public function rewrite(
        string $command,
        array $fragments
    ): self {
        return new self(
            command: $command,
            fragments: $fragments,
            attributes: $this->attributes,
            server: $this->server,
            slingshot: $this->slingshot
        );
    }



    public function parse(): ParameterSet
    {
        $fragments = $this->fragments;
        $arguments = $this->arguments;
        $parameters = [];

        while (!empty($fragments)) {
            $fragment = array_shift($fragments);
            $value = (string)$fragment->value;

            // Value
            if ($fragment->isValue()) {
                foreach ($arguments as $name => $argument) {
                    if (
                        $argument instanceof ValueArgument &&
                        $argument->isValid($value)
                    ) {
                        $parameters[$name] = new ValueParameter(
                            name: $name,
                            value: $value,
                            argument: $argument
                        );

                        unset($arguments[$name]);
                        continue 2;
                    }

                    if (
                        $argument instanceof ValueListArgument &&
                        $argument->isValid($value)
                    ) {
                        $values = [$value];

                        while (
                            !empty($fragments) &&
                            $fragments[0]->isValue() &&
                            $argument->isValid((string)$fragments[0]->value)
                        ) {
                            $nextFragment = array_shift($fragments);
                            $values[] = (string)$nextFragment->value;
                        }

                        $parameters[$name] = new ValueListParameter(
                            name: $name,
                            value: $values,
                            argument: $argument
                        );

                        unset($arguments[$name]);
                        continue 2;
                    }
                }

                if (!isset($parameters['unnamed'])) {
                    $parameters['unnamed'] = new ValueListParameter(
                        name: 'unnamed',
                        value: [],
                        argument: null
                    );
                }

                if ($parameters['unnamed'] instanceof ValueListParameter) {
                    $parameters['unnamed']->addValue($value);
                }

                continue;
            }



            // Option
            if ($fragment->isOption()) {
                $name = (string)$fragment->name;
                $argument = $arguments[$name] ?? null;

                if (
                    $argument instanceof OptionArgument &&
                    $argument->isValid($value)
                ) {
                    $parameters[$name] = new ValueParameter(
                        name: $name,
                        value: $value,
                        argument: $argument
                    );

                    unset($arguments[$name]);
                    continue;
                }

                if (!isset($parameters[$name])) {
                    $parameters[$name] = new ValueParameter(
                        name: $name,
                        value: $value,
                        argument: null
                    );

                    continue;
                }

                $parameter = $parameters[$name];

                if ($parameter instanceof ValueParameter) {
                    if ($parameter->argument) {
                        $parameter->replaceValue($value);
                    } else {
                        $parameter = new ValueListParameter(
                            name: $name,
                            value: [$parameter->value, $value],
                            argument: null
                        );
                    }
                } elseif ($parameter instanceof ValueListParameter) {
                    $parameter->addValue($value);
                } elseif ($parameter instanceof FlagParameter) {
                    $parameter->incrementInstances();
                }

                continue;
            }


            // Shortcut
            if ($fragment->isShortFlag()) {
                foreach ($fragment->shortcuts ?? [] as $shortcut) {
                    foreach ($arguments as $name => $argument) {
                        if (
                            $argument instanceof FlagArgument &&
                            $argument->shortcut === $shortcut
                        ) {
                            $parameters[$name] = new FlagParameter(
                                name: $name,
                                argument: $argument
                            );

                            continue 2;
                        }
                    }

                    $parameters[$shortcut] = new FlagParameter(
                        name: $shortcut,
                        argument: null
                    );
                }

                continue;
            }


            if ($fragment->isLongFlag()) {
                $name = (string)$fragment->name;
                $argument = $arguments[$name] ?? null;

                if ($argument instanceof FlagArgument) {
                    if (
                        isset($parameters[$name]) &&
                        $parameters[$name] instanceof FlagParameter
                    ) {
                        $parameters[$name]->incrementInstances();
                        continue;
                    }

                    $parameters[$name] = new FlagParameter(
                        name: $name,
                        argument: $argument
                    );

                    continue;
                }

                if ($argument instanceof OptionListArgument) {
                    $values = [];

                    while (
                        !empty($fragments) &&
                        $fragments[0]->isValue() &&
                        $argument->isValid((string)$fragments[0]->value)
                    ) {
                        $nextFragment = array_shift($fragments);
                        $values[] = (string)$nextFragment->value;
                    }

                    $parameters[$name] = new ValueListParameter(
                        name: $name,
                        value: $values,
                        argument: $argument
                    );

                    unset($arguments[$name]);
                    continue;
                }

                if (!isset($parameters[$name])) {
                    $parameters[$name] = new FlagParameter(
                        name: $name,
                        argument: null
                    );

                    continue;
                }

                if ($parameters[$name] instanceof FlagParameter) {
                    $parameters[$name]->incrementInstances();
                    continue;
                }
            }
        }


        foreach ($arguments as $name => $argument) {
            if (isset($parameters[$name])) {
                continue;
            }

            if ($argument->default !== null) {
                if (
                    $argument instanceof ValueArgument ||
                    $argument instanceof OptionArgument
                ) {
                    $parameters[$name] = new ValueParameter(
                        name: $name,
                        value: Coercion::toString($argument->default),
                        argument: $argument
                    );
                } elseif (
                    $argument instanceof ValueListArgument ||
                    $argument instanceof OptionListArgument
                ) {
                    $parameters[$name] = new ValueListParameter(
                        name: $name,
                        // @phpstan-ignore-next-line
                        value: Coercion::toArray($argument->default),
                        argument: $argument
                    );
                }

                continue;
            }

            if ($argument->required) {
                throw Exceptional::InvalidArgument(
                    'Missing required argument "' . $name . '"'
                );
            }
        }


        foreach ($parameters as $name => $parameter) {
            if ($parameter instanceof ValueListParameter) {
                // @phpstan-ignore-next-line
                $min = $parameter->argument?->min ?? 0;

                if ($parameter->argument?->required) {
                    $min = max($min, 1);
                }

                if (count($parameter->value) < $min) {
                    throw Exceptional::InvalidArgument(
                        'Parameter "' . $name . '" requires at least ' .
                        // @phpstan-ignore-next-line
                        ($parameter->argument?->min ?? 0) . ' values'
                    );
                }

                if (
                    // @phpstan-ignore-next-line
                    ($parameter->argument?->max ?? null) !== null &&
                    count($parameter->value) > ($parameter->argument?->max)
                ) {
                    throw Exceptional::InvalidArgument(
                        'Parameter "' . $name . '" requires at most ' .
                        ($parameter->argument?->max) . ' values'
                    );
                }

                continue;
            }
        }

        // @phpstan-ignore-next-line
        return $this->parameters = new ParameterSet($parameters);
    }
}
