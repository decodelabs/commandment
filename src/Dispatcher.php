<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment;

use DecodeLabs\Archetype;
use DecodeLabs\Commandment\Request\Fragment;
use DecodeLabs\Commandment\Middleware\Help as HelpMiddleware;
use DecodeLabs\Exceptional;
use DecodeLabs\Slingshot;
use DecodeLabs\Terminus;
use DecodeLabs\Terminus\Session;
use ReflectionAttribute;
use ReflectionClass;

class Dispatcher
{
    /**
     * @var list<Middleware>
     */
    protected(set) array $middleware = [];

    protected(set) Slingshot $slingshot;

    public function __construct()
    {
        $this->slingshot = new Slingshot();

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
        return new Request(
            command: $command,
            fragments: $arguments,
            attributes: $attributes,
            server: $server,
            slingshot: $slingshot ?? $this->slingshot
        );
    }


    public function addMiddleware(
        Middleware $middleware
    ): void {
        $this->middleware[] = $middleware;
    }


    public function dispatch(
        Request $request
    ): bool {
        if(
            !$request->slingshot->hasType(Session::class) &&
            class_exists(Terminus::class)
        ) {
            $request->slingshot->addType(
                Terminus::getSession()
            );
        }

        uasort($this->middleware, function(
            Middleware $a,
            Middleware $b
        ) {
            return $a->priority <=> $b->priority;
        });

        foreach($this->middleware as $middleware) {
            $request = $middleware->handle($request);
        }

        $command = str_replace('-', ' ', $request->command);
        $command = ucwords($command);
        $command = str_replace(' ', '', $command);

        if(!$class = Archetype::tryResolve(Action::class, $command)) {
            throw Exceptional::NotFound(
                'Command not found: ' . $command,
                data: $request
            );
        }

        $ref = new ReflectionClass($class);

        $attributes = $ref->getAttributes(
            Argument::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach($attributes as $attribute) {
            $argument = $attribute->newInstance();
            $request = $request->withArgument($argument);
        }

        $action = $request->slingshot->newInstance(
            $class,
            array_merge($request->attributes, [
                'request' => $request
            ])
        );

        return $action->execute($request);
    }
}
