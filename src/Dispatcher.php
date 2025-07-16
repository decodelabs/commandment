<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment;

use DecodeLabs\Archetype;
use DecodeLabs\Commandment\Middleware\Help as HelpMiddleware;
use DecodeLabs\Commandment\Request\Fragment;
use DecodeLabs\Exceptional;
use DecodeLabs\Slingshot;
use DecodeLabs\Terminus\Session;
use ReflectionAttribute;
use ReflectionClass;

class Dispatcher
{
    /**
     * @var list<Middleware>
     */
    public protected(set) array $middleware = [];

    public protected(set) Slingshot $slingshot;

    /**
     * @var array<string,mixed>|null
     */
    public protected(set) ?array $server = null;

    /**
     * @param array<string,mixed>|null $server
     */
    public function __construct(
        ?array $server = null
    ) {
        $this->slingshot = new Slingshot();
        $this->server = $server;

        $this->addMiddleware(
            new HelpMiddleware()
        );
    }

    /**
     * @param list<string|Fragment> $arguments
     * @param array<string,mixed> $attributes
     * @param array<string,mixed> $server
     */
    public function newRequest(
        string $command,
        array $arguments = [],
        array $attributes = [],
        ?array $server = null,
        ?Slingshot $slingshot = null
    ): Request {
        $output = new Request(
            command: $command,
            fragments: $arguments,
            attributes: $attributes,
            server: $server ?? $this->server,
            slingshot: $slingshot ?? $this->slingshot
        );

        $output->slingshot->addType($this);
        return $output;
    }


    public function addMiddleware(
        Middleware $middleware
    ): void {
        $this->middleware[] = $middleware;
    }


    public function dispatch(
        Request $request
    ): bool {
        if (
            !$request->slingshot->hasType(Session::class) &&
            class_exists(Session::class)
        ) {
            $request->slingshot->addType(
                Session::getDefault()
            );
        }

        uasort($this->middleware, function (
            Middleware $a,
            Middleware $b
        ) {
            return $a->priority <=> $b->priority;
        });

        foreach ($this->middleware as $middleware) {
            $request = $middleware->handle($request);
        }

        if (!$class = $this->getActionClass($request->command)) {
            throw Exceptional::NotFound(
                'Command not found: ' . $request->command,
                data: $request
            );
        }

        foreach ($this->getActionAttributes($class) as $attribute) {
            $argument = $attribute->newInstance();
            $request = $request->withArgument($argument);
        }

        $request->parse();

        $action = $request->slingshot->newInstance(
            $class,
            array_merge($request->attributes, [
                'request' => $request
            ])
        );

        return $action->execute($request);
    }

    /**
     * @param class-string<Action> $class
     * @return list<ReflectionAttribute<Argument>>
     */
    public static function getActionAttributes(
        string $class
    ): array {
        $ref = new ReflectionClass($class);
        $attributes = [];

        if (null !== ($constructor = $ref->getConstructor())) {
            $attributes = $constructor->getAttributes(
                Argument::class,
                ReflectionAttribute::IS_INSTANCEOF
            );
        }

        foreach ($ref->getAttributes(
            Argument::class,
            ReflectionAttribute::IS_INSTANCEOF
        ) as $attribute) {
            $attributes[] = $attribute;
        }

        return $attributes;
    }

    public function hasAction(
        string $name
    ): bool {
        return $this->getActionClass($name) !== null;
    }

    /**
     * @return class-string<Action>|null
     */
    public function getActionClass(
        string $name
    ): ?string {
        $name = str_replace(['-', '/'], [' ', ' \\ '], $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        return Archetype::tryResolve(Action::class, $name);
    }
}
