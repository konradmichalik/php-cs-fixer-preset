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

namespace KonradMichalik\PhpCsFixerPreset\Tests\Package;

use KonradMichalik\PhpCsFixerPreset\Package\CopyrightRange;
use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * CopyrightRangeTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class CopyrightRangeTest extends TestCase
{
    public function testImplementsStringable(): void
    {
        $range = CopyrightRange::create();

        self::assertInstanceOf(Stringable::class, $range);
    }

    public function testCreateReturnsCurrentYearWithoutParameter(): void
    {
        $range = CopyrightRange::create();
        $currentYear = (int) date('Y');

        self::assertNull($range->from);
        self::assertSame($currentYear, $range->to);
    }

    public function testCreateAcceptsSpecificYear(): void
    {
        $range = CopyrightRange::create(2025);

        self::assertNull($range->from);
        self::assertSame(2025, $range->to);
    }

    public function testFromCreatesRangeWithCurrentYearAsTo(): void
    {
        $range = CopyrightRange::from(2020);
        $currentYear = (int) date('Y');

        self::assertSame(2020, $range->from);
        self::assertSame($currentYear, $range->to);
    }

    public function testFromAcceptsSpecificToYear(): void
    {
        $range = CopyrightRange::from(2020, 2025);

        self::assertSame(2020, $range->from);
        self::assertSame(2025, $range->to);
    }

    public function testToStringReturnsSingleYearWhenFromIsNull(): void
    {
        $range = CopyrightRange::create(2025);

        self::assertSame('2025', (string) $range);
    }

    public function testToStringReturnsRangeWhenFromAndToDiffer(): void
    {
        $range = CopyrightRange::from(2020, 2025);

        self::assertSame('2020-2025', (string) $range);
    }

    public function testToStringReturnsSingleYearWhenFromAndToAreEqual(): void
    {
        $range = CopyrightRange::from(2025, 2025);

        self::assertSame('2025', (string) $range);
    }

    public function testToStringWorks(): void
    {
        $range = CopyrightRange::from(2020, 2025);

        self::assertSame('2020-2025', $range->__toString());
    }

    public function testFromUsesCurrentYearWhenToIsNotProvided(): void
    {
        $range = CopyrightRange::from(2020);
        $currentYear = (int) date('Y');

        self::assertSame((string) $currentYear, (string) $range->to);
    }

    public function testCreateReturnsInstanceWithOnlyToYear(): void
    {
        $range = CopyrightRange::create(2025);

        self::assertInstanceOf(CopyrightRange::class, $range);
        self::assertNull($range->from);
        self::assertSame(2025, $range->to);
    }

    public function testFromReturnsInstanceWithFromAndToYears(): void
    {
        $range = CopyrightRange::from(2020, 2025);

        self::assertInstanceOf(CopyrightRange::class, $range);
        self::assertSame(2020, $range->from);
        self::assertSame(2025, $range->to);
    }

    public function testStringRepresentationInStringContext(): void
    {
        $range = CopyrightRange::from(2020, 2025);
        $text = "Copyright {$range}";

        self::assertSame('Copyright 2020-2025', $text);
    }
}
