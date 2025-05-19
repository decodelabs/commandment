<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Action;

use DecodeLabs\Archetype;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Argument\Flag as FlagArgument;
use DecodeLabs\Commandment\Argument\Option as OptionArgument;
use DecodeLabs\Commandment\Argument\OptionList as OptionListArgument;
use DecodeLabs\Commandment\Argument\Value as ValueArgument;
use DecodeLabs\Commandment\Argument\ValueList as ValueListArgument;
use DecodeLabs\Commandment\Description;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Terminus\Session;
use ReflectionAttribute;
use ReflectionClass;

#[Argument\Value(
    name: 'action',
    required: true,
    default: 'help',
    description: 'The action to get help for',
)]
class Help implements Action
{
    public function __construct(
        protected Session $io,
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $rawCommand = $request->parameters->getAsString('action');
        $command = str_replace('-', ' ', (string)$rawCommand);
        $command = ucwords($command);
        $command = str_replace(' ', '', $command);

        if(!$class = Archetype::tryResolve(
            Action::class,
            $command
        )) {
            $this->io->newLine();
            $this->io->write('Command not found: ');
            $this->io->{'.brightRed'}($rawCommand);
            $this->io->newLine();

            return false;
        }

        $ref = new ReflectionClass($class);

        $description = null;
        $arguments = [];

        foreach($ref->getAttributes(
            Argument::class,
            ReflectionAttribute::IS_INSTANCEOF
        ) as $attribute) {
            $arguments[] = $attribute->newInstance();
        }

        foreach($ref->getAttributes(
            Description::class,
        ) as $attribute) {
            $description = $attribute->newInstance();
        }

        $this->io->newLine();
        $this->io->writeLine('Command:');
        $this->io->{'.>brightMagenta'}($rawCommand);
        $this->io->newLine();

        if($description !== null) {
            $this->io->newLine();
            $this->io->writeLine('Description:');
            $this->io->{'.>brightCyan'}($description->description);
            $this->io->newLine();

            if($description->usage !== null) {
                $this->io->writeLine('Usage:');
                $this->io->{'.>brightBlue'}($description->usage);
                $this->io->newLine();
            }

            if(!empty($description->examples)) {
                $this->io->writeLine('Examples:');

                foreach($description->examples as $example) {
                    $this->io->{'.>brightBlue'}($example);
                }

                $this->io->newLine();
            }
        }

        if(!empty($arguments)) {
            $this->io->writeLine('Arguments:');
            $argStrings = [];
            $maxLength = 0;

            foreach($arguments as $argument) {
                $key = $argument->name;

                if($argument instanceof ValueListArgument) {
                    $key = $argument->name . ' ..';
                } else if(!$argument instanceof ValueArgument) {
                    $key = '--' . $argument->name;

                    if($argument instanceof OptionArgument) {
                        $key = '--' . $argument->name . '=<'.$argument->name.'>';
                    } else if($argument instanceof OptionListArgument) {
                        $key = '--' . $argument->name . ' ..';
                    }

                    if(($argument->shortcut ?? null) !== null) {
                        $key = '-' . $argument->shortcut . ', ' . $key;
                    } else {
                        $key = '    ' . $key;
                    }
                }

                $argStrings[$key] = $argument->description;
                $len = strlen($key);

                if($len > $maxLength) {
                    $maxLength = $len;
                }
            }

            foreach($argStrings as $key => $description) {
                $key = str_pad($key, $maxLength, ' ', STR_PAD_RIGHT);
                $this->io->{'>brightGreen'}($key.' ');
                $this->io->{'.>brightCyan'}($description);
            }
        }

        $this->io->newLine();

        return true;
    }
}
