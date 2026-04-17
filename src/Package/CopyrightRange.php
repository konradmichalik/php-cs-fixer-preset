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

use function date;
use function sprintf;

/**
 * CopyrightRange.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final readonly class CopyrightRange implements Stringable
{
    private function __construct(
        public ?int $from,
        public ?int $to,
    ) {}

    public function __toString(): string
    {
        if ($this->from === $this->to) {
            return (string) $this->to;
        }

        if (null !== $this->from && null !== $this->to) {
            return sprintf('%d-%d', $this->from, $this->to);
        }

        return (string) $this->to;
    }

    public static function create(?int $year = null): self
    {
        return new self(null, $year ?? self::getCurrentYear());
    }

    public static function from(int $year, ?int $to = null): self
    {
        return new self($year, $to ?? self::getCurrentYear());
    }

    private static function getCurrentYear(): int
    {
        return (int) date('Y');
    }
}
