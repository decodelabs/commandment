<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Dispatcher;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Terminus\Session;

#[Argument\Value(
    name: 'action',
    required: true,
    description: 'The action to check',
)]
class ActionExists implements Action
{
    public function __construct(
        protected Session $io,
        protected Dispatcher $dispatcher
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $action = $request->parameters->asString('action');

        if ($this->dispatcher->hasAction($action)) {
            $this->io->writeLine('true');
        } else {
            $this->io->writeLine('false');
        }

        return true;
    }
}
