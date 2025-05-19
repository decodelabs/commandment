<?php

/**
 * @package Commandment
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Commandment;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Description
{
    /**
     * @param ?array<string> $examples
     */
    public function __construct(
        protected(set) string $description,
        protected(set) ?string $usage = null,
        protected(set) ?array $examples = null,
    ) {
    }
}
