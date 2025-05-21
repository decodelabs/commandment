<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Middleware;

use DecodeLabs\Commandment\Middleware;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Commandment\Request\Fragment;
use DecodeLabs\Terminus\Session;

class Help implements Middleware
{
    public int $priority = 99;

    public function __construct(
        ?int $priority = null
    ) {
        if ($priority !== null) {
            $this->priority = $priority;
        }
    }

    public function handle(
        Request $request
    ): Request {
        if(!class_exists(Session::class)) {
            return $request;
        }

        foreach($request->fragments as $fragment) {
            if ($fragment->body === '--help') {
                return $this->rewriteRequest(
                    $request,
                );
            }
        }

        return $request;
    }

    private function rewriteRequest(
        Request $request
    ): Request {
        return $request->rewrite(
            command: 'help',
            fragments: [
                new Fragment($request->command),
            ]
        );
    }
}
