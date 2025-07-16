<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment\Argument;

trait ValueTrait
{
    /**
     * @var ?list<string>
     */
    public protected(set) ?array $options = null;


    public function isValid(
        string $value
    ): bool {
        if ($this->options === null) {
            return true;
        }

        return in_array($value, $this->options, true);
    }
}
