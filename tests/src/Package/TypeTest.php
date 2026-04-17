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

use KonradMichalik\PhpCsFixerPreset\Package\Type;
use PHPUnit\Framework\TestCase;
use ReflectionEnum;

/**
 * TypeTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class TypeTest extends TestCase
{
    public function testIsEnum(): void
    {
        self::assertTrue(enum_exists(Type::class));
    }

    public function testIsBackedByString(): void
    {
        self::assertSame('string', (new ReflectionEnum(Type::class))->getBackingType()?->getName());
    }

    public function testComposerPackageCase(): void
    {
        self::assertSame('Composer package', Type::ComposerPackage->value);
    }

    public function testComposerPluginCase(): void
    {
        self::assertSame('Composer plugin', Type::ComposerPlugin->value);
    }

    public function testSymfonyProjectCase(): void
    {
        self::assertSame('Symfony project', Type::SymfonyProject->value);
    }

    public function testTYPO3ExtensionCase(): void
    {
        self::assertSame('TYPO3 CMS extension', Type::TYPO3Extension->value);
    }

    public function testTYPO3ProjectCase(): void
    {
        self::assertSame('TYPO3 CMS project', Type::TYPO3Project->value);
    }

    public function testHasAllExpectedCases(): void
    {
        $cases = Type::cases();

        self::assertCount(5, $cases);
        self::assertContains(Type::ComposerPackage, $cases);
        self::assertContains(Type::ComposerPlugin, $cases);
        self::assertContains(Type::SymfonyProject, $cases);
        self::assertContains(Type::TYPO3Extension, $cases);
        self::assertContains(Type::TYPO3Project, $cases);
    }

    public function testCanBeUsedInStringContext(): void
    {
        $type = Type::ComposerPackage;
        $text = "This is a {$type->value}";

        self::assertSame('This is a Composer package', $text);
    }

    public function testFromStringConversion(): void
    {
        $type = Type::from('Composer package');

        self::assertSame(Type::ComposerPackage, $type);
    }

    public function testTryFromStringConversion(): void
    {
        $type = Type::tryFrom('Composer package');

        self::assertSame(Type::ComposerPackage, $type);
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $type = Type::tryFrom('Invalid package type');

        self::assertNull($type);
    }

    public function testFromComposerTypeReturnsComposerPlugin(): void
    {
        $type = Type::fromComposerType('composer-plugin');

        self::assertSame(Type::ComposerPlugin, $type);
    }

    public function testFromComposerTypeReturnsSymfonyProject(): void
    {
        $type = Type::fromComposerType('symfony-bundle');

        self::assertSame(Type::SymfonyProject, $type);
    }

    public function testFromComposerTypeReturnsTYPO3Extension(): void
    {
        $type = Type::fromComposerType('typo3-cms-extension');

        self::assertSame(Type::TYPO3Extension, $type);
    }

    public function testFromComposerTypeReturnsComposerPackageForUnknownType(): void
    {
        $type = Type::fromComposerType('some-unknown-type');

        self::assertSame(Type::ComposerPackage, $type);
    }

    public function testFromComposerTypeReturnsComposerPackageForLibrary(): void
    {
        $type = Type::fromComposerType('library');

        self::assertSame(Type::ComposerPackage, $type);
    }
}
