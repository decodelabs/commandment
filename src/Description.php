<?php

/**
 * Commandment
 * @license https://opensource.org/licenses/MIT
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
        public protected(set) string $description,
        public protected(set) ?string $usage = null,
        public protected(set) ?array $examples = null,
    ) {
    }
}
