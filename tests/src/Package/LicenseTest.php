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

use KonradMichalik\PhpCsFixerPreset\Package\License;
use PHPUnit\Framework\TestCase;

/**
 * LicenseTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class LicenseTest extends TestCase
{
    public function testProprietaryReturnsEmptyLicenseText(): void
    {
        self::assertSame('', License::Proprietary->licenseText());
    }

    public function testGpl2OrLaterReturnsGpl2Text(): void
    {
        $text = License::GPL2OrLater->licenseText();

        self::assertStringContainsString('either version 2 of the License', $text);
        self::assertStringContainsString('GNU General Public License', $text);
        self::assertStringContainsString('https://www.gnu.org/licenses/', $text);
    }

    public function testGpl3OrLaterReturnsGpl3Text(): void
    {
        $text = License::GPL3OrLater->licenseText();

        self::assertStringContainsString('either version 3 of the License', $text);
        self::assertStringContainsString('GNU General Public License', $text);
        self::assertStringContainsString('https://www.gnu.org/licenses/', $text);
    }

    public function testGpl2AndGpl3TextsDiffer(): void
    {
        self::assertNotSame(
            License::GPL2OrLater->licenseText(),
            License::GPL3OrLater->licenseText(),
        );
    }
}
