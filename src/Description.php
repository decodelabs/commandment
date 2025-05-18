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
    public function __construct(
        public string $description
    ) {
    }
}
