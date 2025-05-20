# Commandment

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/commandment?style=flat)](https://packagist.org/packages/decodelabs/commandment)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/commandment.svg?style=flat)](https://packagist.org/packages/decodelabs/commandment)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/commandment.svg?style=flat)](https://packagist.org/packages/decodelabs/commandment)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/commandment/integrate.yml?branch=develop)](https://github.com/decodelabs/commandment/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/commandment?style=flat)](https://packagist.org/packages/decodelabs/commandment)

### Console command interface

Commandment provides a unified system for building and dispatching console actions, mirroring the dispatcher and middleware stack of [Harvest](https://github.com/decodelabs/harvest).

---

## Installation

Install via Composer:

```bash
composer require decodelabs/commandment
```

## Usage

Build your Action to interact with the command line. The `Request` object provides the raw console arguments and the means to parse them in a structured way.

Use `Argument` attributes on your Action class to define the arguments you want to accept.
Constructor arguments are automatically injected into your Action class - import the Terminus `Session` to write to the output stream to keep your Actions portable.

```php
namespace MyThing\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Terminus\Session;

#[Argument\Value(
    name: 'input',
    description: 'Input value',
    required: true,
    default: 'default'
)]
#[Argument\Flag(
    name: 'verbose',
    shortcut: 'v',
    description: 'Enable verbose output'
)]
#[Argument\Option(
    name: 'potatoes',
    shortcut: 'p',
    description: 'How many potatoes?',
    default: 5
)]
class MyAction implements Action
{
    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $this->io->writeLine('Hello world!');

        $this->io->writeLine('Input: '. $request->parameters->getAsString('input'));

        if($request->parameters->getAsBool('verbose')) {
            $this->io->writeLine('Verbose output enabled');

            for($potato = 0; $potato < $request->parameters->getAsInt('potatoes'); $potato++) {
                $this->io->writeLine('Potato #'. ($potato + 1));
            }
        }

        return true;
    }
}
```

```bash
effigy my-action "this is my input" -v --potatoes=3
```

### Dispatching

To run your Action, create a `Dispatcher` and a `Request` object, then call the `dispatch()` method:

```php
use DecodeLabs\Commandment\Dispatcher;

$dispatcher = new Dispatcher();

$request = $dispatcher->newRequest(
    command: 'my-action',
    arguments: [
        'this is my input',
        '-v',
        '--potatoes=3'
    ],
    attributes: [
        'arbitrary' => 'data'
    ],
    server: [
        'override' => '$_SERVER'
    ]
);

$dispatcher->dispatch($request);
```

If you want to provide extra objects for dependency injection, you can add them to the `Slingshot` instance, either on the `Dispatcher` or on the `Request` object:

```php
use DecodeLabs\Commandment\Dispatcher;
use MyThing\PotatoPeeler;
use MyThing\PotatoMasher;

$dispatcher = new Dispatcher();

$dispatcher->slingshot->addType(new PotatoPeeler());

$request = $dispatcher->newRequest('my-action', ['input ...']);
$request->slingshot->addType(new PotatoMasher());
$request->slingshot->addParameter([
    'arbitrary' => 'data'
]);

$dispatcher->dispatch($request);
```

You can then reference these types in your Action constructor:

```php
namespace MyThing\Action;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Terminus\Session;
use MyThing\PotatoPeeler;
use MyThing\PotatoMasher;

class MyAction implements Action
{
    public function __construct(
        protected Session $io,
        protected PotatoPeeler $peeler,
        protected PotatoMasher $masher,
        protected string $arbitrary
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        // Do the thing
        return true;
    }
}
```

### Middleware

Commandment supports simple middleware which can be used to modify the request before the Action is executed.
It doesn't need to handle the `$next` middleware like traditional middleware as the CLI context doesn't require traditional response handling. Instead, just return a modified `Request` object.

```php
use DecodeLabs\Commandment\Middleware;

class MyMiddleware implements Middleware
{
    public function handle(
        Request $request,
    ): Request {
        // Do something with the request

        $request = $request->rewrite(
            command: 'redirected-action',
            arguments: [
                'new-argument'
            ],
        );

        return $request;
    }
}
```

Add the middleware to the dispatcher before dispatching:

```php
use DecodeLabs\Commandment\Dispatcher;
use MyThing\Middleware\MyMiddleware;

$dispatcher = new Dispatcher();
$dispatcher->addMiddleware(new MyMiddleware());
$request = $dispatcher->newRequest('my-action', ['input ...']);
$dispatcher->dispatch($request);
```

## Licensing

Commandment is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
