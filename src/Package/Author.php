<?php

declare(strict_types=1);

/*
 * This file is part of the "php-cs-fixer-preset" Composer package.
 *
 * (c) 2025-2026 Konrad Michalik <hej@konradmichalik.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KonradMichalik\PhpCsFixerPreset\Package;

use Stringable;

use function sprintf;

/**
 * Author.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final readonly class Author implements Stringable
{
    private function __construct(
        public string $name,
        public string $emailAddress,
    ) {}

    public function __toString(): string
    {
        return sprintf('%s <%s>', $this->name, $this->emailAddress);
    }

    public static function create(
        string $name,
        string $emailAddress,
    ): self {
        return new self($name, $emailAddress);
    }
}
