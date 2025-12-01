# Commandment — Package Specification

> **Cluster:** `cli`
> **Language:** `php`
> **Milestone:** `m2`
> **Repo:** `https://github.com/decodelabs/commandment`
> **Role:** CLI dispatcher

This document describes the purpose, contracts, and design of **Commandment** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Commandment in their own applications or libraries.
- Contributors **maintaining or extending** Commandment.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Commandment provides a unified system for building and dispatching console actions, mirroring the dispatcher and middleware stack pattern of Harvest (HTTP stack). It offers a structured approach to defining CLI commands using PHP attributes, parsing command-line arguments, and executing actions with dependency injection support. The package provides argument parsing, middleware support, and automatic help generation.

### 1.2 Non-Goals

Commandment does **not**:

- Provide terminal I/O capabilities — it depends on Terminus for that
- Handle process management or signal handling — that's handled by runtime layers
- Provide command routing or discovery — it uses Archetype for action resolution
- Handle command-line history or completion — that's handled by the shell
- Provide interactive prompts or confirmations — that's handled by Terminus
- Handle command aliasing or shortcuts beyond argument shortcuts
- Provide command grouping or namespacing beyond Archetype's mapping

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `cli` (see Chorus taxonomy)
- Commandment is a CLI dispatcher package that provides the command interface layer for console applications. It sits in the CLI cluster alongside Terminus (I/O) and Clip (runtime integration). It depends on Archetype for action resolution, Coercion for type conversion, and Slingshot for dependency injection. It's used by Clip, Effigy, and Zest for CLI command handling.

### 2.2 Typical Usage Contexts

Typical places Commandment appears:

- CLI application command dispatching
- Console tool command handling
- Build script command execution
- Development tool command interfaces
- Any application that needs structured CLI command handling

Commandment is intended to be used whenever code needs to define and execute CLI commands with structured argument parsing, dependency injection, and middleware support.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Commandment\Action`
  Interface for CLI actions. Defines `execute(Request $request): bool` method.

- `DecodeLabs\Commandment\Dispatcher`
  Main dispatcher class. Handles action resolution, middleware execution, argument parsing, and action instantiation.

- `DecodeLabs\Commandment\Request`
  Request object representing a CLI command invocation. Contains command name, fragments (raw arguments), parsed parameters, attributes, server variables, and Slingshot instance.

- `DecodeLabs\Commandment\Middleware`
  Interface for middleware. Defines `handle(Request $request): Request` method and `priority` property.

- `DecodeLabs\Commandment\Argument`
  Base interface for argument definitions. Implemented by argument attribute classes.

- `DecodeLabs\Commandment\Argument\Value`
  Attribute for positional value arguments.

- `DecodeLabs\Commandment\Argument\ValueList`
  Attribute for positional value list arguments (multiple values).

- `DecodeLabs\Commandment\Argument\Flag`
  Attribute for boolean flag arguments (`--flag` or `-f`).

- `DecodeLabs\Commandment\Argument\Option`
  Attribute for option arguments with values (`--option=value` or `-o value`).

- `DecodeLabs\Commandment\Argument\OptionList`
  Attribute for option list arguments (multiple values).

- `DecodeLabs\Commandment\Description`
  Attribute for action descriptions, usage examples, and help text.

- `DecodeLabs\Commandment\Request\ParameterSet`
  Collection of parsed parameters. Provides type-safe accessors (`asString`, `asInt`, `tryBool`, etc.).

- `DecodeLabs\Commandment\Request\Fragment`
  Represents a single command-line argument fragment (value, flag, option).

- `DecodeLabs\Commandment\Action\Help`
  Built-in help action that displays command documentation.

- `DecodeLabs\Commandment\Middleware\Help`
  Built-in middleware that intercepts `--help` flags and redirects to help action.

### 3.2 Main Entry Points

The main usage pattern is through the `Dispatcher`:

```php
use DecodeLabs\Commandment\Dispatcher;
use DecodeLabs\Archetype;

$dispatcher = new Dispatcher($archetype);
$request = $dispatcher->newRequest('my-action', ['arg1', '--flag']);
$dispatcher->dispatch($request);
```

---

## 4. Dependencies

### 4.1 Decode Labs

- `decodelabs/archetype` (required)
  Used for action class resolution. Maps action names to class names.

- `decodelabs/coercion` (required)
  Used for type coercion in parameter accessors (`asString`, `asInt`, etc.).

- `decodelabs/exceptional` (required)
  Used for exception handling when parsing fails or actions are not found.

- `decodelabs/nuance` (required)
  Used for debugging and inspection via `Dumpable` interface on `ParameterSet`.

- `decodelabs/slingshot` (required)
  Used for dependency injection when instantiating actions. Actions can request dependencies via constructor injection.

### 4.2 External

- None

### 4.3 Optional Integrations

- `decodelabs/terminus` (optional, dev dependency)
  Detected at runtime if installed, used for I/O operations. The dispatcher automatically provides a default `Session` instance if Terminus is available. Actions can request `Terminus\Session` in their constructors for portable I/O.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- Actions are resolved via Archetype using action name (converted from kebab-case to PascalCase)
- Arguments are defined using PHP attributes on action classes
- Middleware is executed in priority order (lowest first)
- Request parsing happens after middleware execution
- Actions are instantiated via Slingshot with dependency injection
- Parameter accessors use Coercion for type conversion
- Missing required arguments throw exceptions
- Default values are applied for optional arguments
- Flags can be specified multiple times (counted)
- Options can be specified multiple times (last value wins, or converted to list)

### 5.2 Input & Output Contracts

**Dispatcher Operations:**
- `newRequest(string $command, array $arguments, array $attributes, ?array $server, ?Slingshot $slingshot): Request` — Creates new request
- `addMiddleware(Middleware $middleware): void` — Adds middleware to stack
- `dispatch(Request $request): bool` — Dispatches request, returns action result
- `hasAction(string $name): bool` — Checks if action exists
- `getActionClass(string $name): ?string` — Gets action class name
- `getActionAttributes(string $class): array` — Gets argument attributes from action class

**Request Operations:**
- `parse(): ParameterSet` — Parses fragments into parameters (lazy)
- `rewrite(string $command, array $fragments): static` — Creates new request with different command
- `withArgument(Argument $argument): static` — Adds argument definition
- `withAttribute(string $name, mixed $value): static` — Adds attribute
- `getAttribute(string $name, mixed $default): mixed` — Gets attribute
- `getServerParam(string $key): mixed` — Gets server variable

**ParameterSet Operations:**
- `has(string $name): bool` — Checks if parameter exists
- `get(string $name): ?Parameter` — Gets parameter
- `tryString(string $name): ?string` — Gets string value (nullable)
- `asString(string $name): string` — Gets string value (throws if missing)
- `tryInt(string $name): ?int` — Gets integer value (nullable)
- `asInt(string $name): int` — Gets integer value (throws if missing)
- `tryBool(string $name): ?bool` — Gets boolean value (nullable)
- `asBool(string $name): bool` — Gets boolean value
- `tryStringList(string $name): ?array` — Gets string list (nullable)
- `asStringList(string $name): array` — Gets string list
- `tryIntList(string $name): ?array` — Gets integer list (nullable)
- `asIntList(string $name): array` — Gets integer list

**Argument Types:**
- **Value**: Positional single value argument
- **ValueList**: Positional multiple value argument (supports min/max)
- **Flag**: Boolean flag (`--flag` or `-f`)
- **Option**: Named option with value (`--option=value` or `-o value`)
- **OptionList**: Named option with multiple values (supports min/max)

**Fragment Parsing:**
- Values: Plain strings (not starting with `-`)
- Short flags: `-abc` (multiple flags)
- Long flags: `--flag`
- Options: `--option=value` or `--option "value"`
- Quoted values: `"value"` or `'value'` (quotes stripped)

### 5.3 Action Resolution

Action names are converted from kebab-case to PascalCase:
- `my-action` → `MyAction`
- `my/action` → `My\Action`
- `my-action` → searches for class via Archetype mapping

### 5.4 Middleware Execution

Middleware is executed in priority order (lowest priority first):
- Each middleware receives the request and returns a (possibly modified) request
- Middleware can rewrite the request (change command, arguments, etc.)
- No traditional `$next` callback — middleware just returns modified request
- Built-in `Help` middleware intercepts `--help` and redirects to help action

### 5.5 Dependency Injection

Actions are instantiated via Slingshot:
- Constructor parameters are resolved from Slingshot
- `Request` is automatically added to attributes
- `Terminus\Session` is automatically provided if Terminus is available
- Custom dependencies can be added to Dispatcher or Request Slingshot instances

---

## 6. Error Handling

- Missing required arguments throw `Exceptional::InvalidArgument`
- Invalid argument values throw `Exceptional::InvalidArgument`
- Action not found throws `Exceptional::NotFound`
- Parameter accessors throw `Exceptional::InvalidArgument` if parameter doesn't exist (for `as*` methods)
- Parameter accessors return `null` for missing parameters (for `try*` methods)
- Value list validation throws `Exceptional::InvalidArgument` if count is outside min/max range
- Action execution returns `bool` (true for success, false for failure)

---

## 7. Configuration & Extensibility

- Actions are registered via Archetype mapping (not directly in Commandment)
- Middleware can be added to dispatcher before dispatching
- Custom argument types can be created by implementing `Argument` interface
- Custom middleware can be created by implementing `Middleware` interface
- Request attributes can be used to pass arbitrary data to actions
- Server variables can be overridden per-request

---

## 8. Interactions with Other Packages

### 8.1 Archetype

Commandment uses Archetype to resolve action class names from action names. Action names are converted to class names and resolved via Archetype's mapping system.

### 8.2 Coercion

Commandment uses Coercion for type conversion in parameter accessors. All `as*` and `try*` methods use Coercion to convert string values to appropriate types.

### 8.3 Exceptional

Commandment uses Exceptional for all exception handling, providing consistent error reporting across the ecosystem.

### 8.4 Nuance

Commandment implements Nuance's `Dumpable` interface on `ParameterSet`, allowing parameter sets to be inspected and debugged using Nuance's debugging tools.

### 8.5 Slingshot

Commandment uses Slingshot for dependency injection when instantiating actions. Actions can request any type registered in Slingshot via constructor injection.

### 8.6 Terminus

Commandment detects Terminus at runtime and automatically provides a default `Session` instance if available. Actions can request `Terminus\Session` in their constructors for portable I/O operations.

---

## 9. Usage Examples

### 9.1 Basic Action Definition

```php
namespace MyThing\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Terminus\Session;

#[Argument\Value(
    name: 'input',
    required: true,
    description: 'Input value'
)]
#[Argument\Flag(
    name: 'verbose',
    shortcut: 'v',
    description: 'Enable verbose output'
)]
class MyAction implements Action
{
    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(Request $request): bool {
        $input = $request->parameters->asString('input');
        $verbose = $request->parameters->asBool('verbose');
        
        $this->io->writeLine('Input: ' . $input);
        
        if ($verbose) {
            $this->io->writeLine('Verbose mode enabled');
        }
        
        return true;
    }
}
```

### 9.2 Dispatcher Usage

```php
use DecodeLabs\Archetype;
use DecodeLabs\Commandment\Dispatcher;
use DecodeLabs\Monarch;

$archetype = Monarch::getService(Archetype::class);
$dispatcher = new Dispatcher($archetype);

$request = $dispatcher->newRequest(
    command: 'my-action',
    arguments: ['input-value', '-v']
);

$result = $dispatcher->dispatch($request);
```

### 9.3 Custom Middleware

```php
use DecodeLabs\Commandment\Middleware;
use DecodeLabs\Commandment\Request;

class MyMiddleware implements Middleware
{
    public int $priority = 10;

    public function handle(Request $request): Request {
        // Modify request
        return $request->withAttribute('custom', 'value');
    }
}

$dispatcher->addMiddleware(new MyMiddleware());
```

### 9.4 Dependency Injection

```php
use MyThing\Service;

class MyAction implements Action
{
    public function __construct(
        protected Session $io,
        protected Service $service
    ) {
    }

    public function execute(Request $request): bool {
        $this->service->doSomething();
        return true;
    }
}

// Register service
$dispatcher->slingshot->addType(new Service());
```

### 9.5 Argument Types

```php
#[Argument\Value(name: 'input', required: true)]
#[Argument\ValueList(name: 'files', min: 1, max: 10)]
#[Argument\Flag(name: 'force', shortcut: 'f')]
#[Argument\Option(name: 'output', shortcut: 'o', required: true)]
#[Argument\OptionList(name: 'tags', min: 1)]
class MyAction implements Action
{
    public function execute(Request $request): bool {
        $input = $request->parameters->asString('input');
        $files = $request->parameters->asStringList('files');
        $force = $request->parameters->asBool('force');
        $output = $request->parameters->asString('output');
        $tags = $request->parameters->asStringList('tags');
        
        // Process...
        return true;
    }
}
```

### 9.6 Help Action

```php
#[Description(
    description: 'My custom action',
    usage: 'my-action <input> [options]',
    examples: [
        'my-action "value"',
        'my-action "value" --verbose'
    ]
)]
class MyAction implements Action
{
    // Action implementation
}

// Help is automatically available via --help flag
// Or: $dispatcher->dispatch($dispatcher->newRequest('help', ['my-action']));
```

---

## 10. Implementation Notes (for Contributors)

### 10.1 Action Name Resolution

Action names are converted from kebab-case to PascalCase:
- Hyphens and slashes are replaced with spaces
- Words are capitalized
- Spaces are removed
- Result is resolved via Archetype

### 10.2 Argument Attribute Discovery

Argument attributes are discovered from:
- Class-level attributes
- Constructor parameter attributes

Both are collected and merged.

### 10.3 Fragment Parsing

Fragments are parsed sequentially:
- Values are matched to Value/ValueList arguments
- Options are matched to Option/OptionList arguments
- Flags are matched to Flag arguments
- Unmatched values go to 'unnamed' parameter list
- Unmatched flags/options are stored with null argument

### 10.4 Parameter Validation

After parsing:
- Required arguments without values throw exceptions
- Default values are applied for optional arguments
- Value list counts are validated against min/max
- Options are validated against allowed values (if specified)

### 10.5 Middleware Priority

Middleware is sorted by priority (ascending):
- Lower priority numbers execute first
- Built-in Help middleware has priority 99 (executes last)

### 10.6 Slingshot Integration

Slingshot is used for:
- Action instantiation (constructor injection)
- Request-level dependency injection
- Dispatcher-level dependency injection (shared)

Request Slingshot is cloned from Dispatcher Slingshot, allowing per-request overrides.

### 10.7 Terminus Integration

If Terminus is available:
- Default `Session` is automatically added to Slingshot
- Actions can request `Terminus\Session` in constructors
- Help action uses Terminus for formatted output

---

## 11. Testing & Quality

- **Code Quality Score:** 4/5
- **README Quality Score:** 3.5/5
- **Documentation Score:** 0/5 (this spec)
- **Test Coverage Score:** 0/5

See `composer.json` for supported PHP versions.

---

## 12. Roadmap & Future Ideas

- Add support for command aliases
- Add support for command grouping/namespacing
- Add support for interactive prompts
- Add support for command completion
- Improve help generation
- Add test coverage
- Consider adding command discovery utilities
- Consider adding command validation utilities

---

## 13. References

- [Archetype Package](https://github.com/decodelabs/archetype) — Action resolution
- [Coercion Package](https://github.com/decodelabs/coercion) — Type conversion
- [Exceptional Package](https://github.com/decodelabs/exceptional) — Exception handling
- [Nuance Package](https://github.com/decodelabs/nuance) — Debugging tools
- [Slingshot Package](https://github.com/decodelabs/slingshot) — Dependency injection
- [Terminus Package](https://github.com/decodelabs/terminus) — Terminal I/O
- [Harvest Package](https://github.com/decodelabs/harvest) — HTTP dispatcher (similar pattern)
- [Chorus Package Index](../../../chorus/config/packages.json) — Ecosystem metadata

