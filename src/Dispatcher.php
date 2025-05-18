<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment;

use DecodeLabs\Commandment\Request\Fragment;
use DecodeLabs\Slingshot;
use ReflectionAttribute;
use ReflectionClass;

class Dispatcher
{
    protected(set) Slingshot $slingshot;

    public function __construct()
    {
        $this->slingshot = new Slingshot();
    }

    /**
     * @param list<string|Fragment> $arguments
     * @param array<string,mixed> $attributes
     * @param array<string,mixed> $server
     */
    public function newRequest(
        string $command,
        array $arguments,
        array $attributes = [],
        ?array $server = null
    ): Request {
        return new Request(
            command: $command,
            fragments: $arguments,
            attributes: $attributes,
            server: $server
        );
    }

    public function dispatch(
        Request $request
    ): bool {
        $command = str_replace('-', ' ', $request->command);
        $command = ucwords($command);
        $command = str_replace(' ', '', $command);

        $action = $this->slingshot->resolveNamedInstance(
            Action::class,
            $command,
            [
                'request' => $request,
            ]
        );

        $ref = new ReflectionClass($action);

        $attributes = $ref->getAttributes(
            Argument::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach($attributes as $attribute) {
            $argument = $attribute->newInstance();
            $request = $request->withArgument($argument);
        }

        return $action->execute($request);
    }
}
