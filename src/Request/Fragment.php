<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Request;

use Stringable;

class Fragment implements Stringable
{
    public ?string $name {
        get {
            if($this->isOption()) {
                $output = explode('=', $this->body, 2)[0];
            } elseif($this->isLongFlag()) {
                $output = $this->body;
            } else {
                return null;
            }

            return ltrim($output, '-');
        }
    }

    /**
     * @var ?list<string>
     */
    public ?array $shortcuts {
        get {
            if(!$this->isShortFlag()) {
                return null;
            }

            return str_split(ltrim($this->body, '-'));
        }
    }

    public ?string $value {
        get {
            if($this->isValue()) {
                return $this->body;
            }

            if($this->isOption()) {
                if(null === ($output = explode('=', $this->body, 2)[1] ?? null)) {
                    return null;
                }

                if(
                    (
                        str_starts_with($output, '"') &&
                        str_ends_with($output, '"')
                    ) ||
                    (
                        str_starts_with($output, "'") &&
                        str_ends_with($output, "'")
                    )
                ) {
                    $output = substr($output, 1, -1);
                }

                return $output;
            }

            return null;
        }
    }

    public function __construct(
        protected(set) string $body
    ) {
        if(
            (
                str_starts_with($this->body, '"') &&
                str_ends_with($this->body, '"')
            ) ||
            (
                str_starts_with($this->body, "'") &&
                str_ends_with($this->body, "'")
            )
        ) {
            $this->body = substr($this->body, 1, -1);
        }
    }

    public function isShortFlag(): bool
    {
        return (bool)preg_match('/^-([a-zA-Z]+)$/', $this->body);
    }

    public function isLongFlag(): bool
    {
        return (bool)preg_match('/^--([a-zA-Z][a-zA-Z0-9_-]+)$/', $this->body);
    }

    public function isOption(): bool
    {
        return (bool)preg_match('/^--([a-zA-Z][a-zA-Z0-9_-]+)=(.*)$/', $this->body);
    }

    public function isValue(): bool
    {
        return !str_starts_with($this->body, '-');
    }

    public function __toString(): string
    {
        return $this->body;
    }
}
