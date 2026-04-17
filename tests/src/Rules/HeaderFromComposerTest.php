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

namespace KonradMichalik\PhpCsFixerPreset\Tests\Rules;

use JsonException;
use KonradMichalik\PhpCsFixerPreset\Package\{Author, CopyrightRange, Type};
use KonradMichalik\PhpCsFixerPreset\Rules\Header;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * HeaderFromComposerTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class HeaderFromComposerTest extends TestCase
{
    public function testFromComposerReadsFromRealComposerJson(): void
    {
        $header = Header::fromComposer(
            __DIR__.'/../../../composer.json',
            CopyrightRange::from(2025),
        );

        self::assertInstanceOf(Header::class, $header);
        self::assertSame('php-cs-fixer-preset', $header->packageName);
        self::assertSame(Type::ComposerPackage, $header->packageType);
        self::assertNotEmpty($header->packageAuthors);
    }

    public function testFromComposerExtractsPackageNameWithoutVendor(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package-name',
            'type' => 'library',
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertSame('package-name', $header->packageName);

        unlink($tmpFile);
    }

    public function testFromComposerMapsComposerTypeToPackageType(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
            'type' => 'composer-plugin',
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertSame(Type::ComposerPlugin, $header->packageType);

        unlink($tmpFile);
    }

    public function testFromComposerMapsSymfonyBundle(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/bundle',
            'type' => 'symfony-bundle',
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertSame(Type::SymfonyProject, $header->packageType);

        unlink($tmpFile);
    }

    public function testFromComposerMapsTypo3Extension(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/extension',
            'type' => 'typo3-cms-extension',
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertSame(Type::TYPO3Extension, $header->packageType);

        unlink($tmpFile);
    }

    public function testFromComposerExtractsTypo3ExtensionKeyFromExtras(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/typo3-environment-indicator',
            'type' => 'typo3-cms-extension',
            'extra' => [
                'typo3/cms' => [
                    'extension-key' => 'typo3_environment_indicator',
                ],
            ],
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertSame('typo3_environment_indicator', $header->packageName);
        self::assertSame(Type::TYPO3Extension, $header->packageType);

        unlink($tmpFile);
    }

    public function testFromComposerFallsBackToComposerNameForTypo3ExtensionWithoutExtras(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/typo3-extension',
            'type' => 'typo3-cms-extension',
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertSame('typo3-extension', $header->packageName);
        self::assertSame(Type::TYPO3Extension, $header->packageType);

        unlink($tmpFile);
    }

    public function testFromComposerDefaultsToComposerPackage(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
            'type' => 'some-unknown-type',
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertSame(Type::ComposerPackage, $header->packageType);

        unlink($tmpFile);
    }

    public function testFromComposerExtractsAuthors(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
            'authors' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
            ],
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertCount(1, $header->packageAuthors);
        self::assertSame('John Doe', $header->packageAuthors[0]->name);

        unlink($tmpFile);
    }

    public function testFromComposerAllowsOverridingPackageName(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
        ]));

        $header = Header::fromComposer(
            $tmpFile,
            CopyrightRange::from(2025),
            'custom-package-name',
        );

        self::assertSame('custom-package-name', $header->packageName);

        unlink($tmpFile);
    }

    public function testFromComposerAllowsOverridingPackageType(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
            'type' => 'library',
        ]));

        $header = Header::fromComposer(
            $tmpFile,
            CopyrightRange::from(2025),
            null,
            Type::SymfonyProject,
        );

        self::assertSame(Type::SymfonyProject, $header->packageType);

        unlink($tmpFile);
    }

    public function testFromComposerAllowsOverridingAuthors(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
            'authors' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
            ],
        ]));

        $customAuthors = [Author::create('Custom Author', 'custom@example.com')];
        $header = Header::fromComposer(
            $tmpFile,
            CopyrightRange::from(2025),
            null,
            null,
            $customAuthors,
        );

        self::assertCount(1, $header->packageAuthors);
        self::assertSame('Custom Author', $header->packageAuthors[0]->name);

        unlink($tmpFile);
    }

    public function testFromComposerThrowsExceptionWhenFileNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Composer file not found at:');

        Header::fromComposer('/non/existent/composer.json', CopyrightRange::from(2025));
    }

    public function testFromComposerThrowsExceptionWhenFileCannotBeRead(): void
    {
        // On some systems (like macOS with certain security settings),
        // chmod 0000 doesn't prevent reading for the file owner.
        // We skip this test on such systems.
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode(['name' => 'vendor/package']));
        chmod($tmpFile, 0000); // Remove all permissions

        // Test if we can actually prevent reading (system dependent)
        if (false !== @file_get_contents($tmpFile)) {
            chmod($tmpFile, 0644);
            unlink($tmpFile);
            self::markTestSkipped('Cannot reliably remove read permissions on this system');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read composer file at:');

        try {
            @Header::fromComposer($tmpFile, CopyrightRange::from(2025));
        } finally {
            @chmod($tmpFile, 0644); // Restore permissions for cleanup
            @unlink($tmpFile);
        }
    }

    public function testFromComposerThrowsExceptionWhenJsonIsInvalid(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, 'invalid json');

        $this->expectException(JsonException::class);

        try {
            Header::fromComposer($tmpFile, CopyrightRange::from(2025));
        } finally {
            unlink($tmpFile);
        }
    }

    public function testFromComposerHandlesMissingName(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'type' => 'library',
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertSame('', $header->packageName);

        unlink($tmpFile);
    }

    public function testFromComposerHandlesNameWithoutSlash(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'package-only',
        ]));

        $header = Header::fromComposer($tmpFile, CopyrightRange::from(2025));

        self::assertSame('package-only', $header->packageName);

        unlink($tmpFile);
    }

    public function testFromComposerUsesCopyrightRange(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
            'authors' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
            ],
        ]));

        $copyrightRange = CopyrightRange::from(2020, 2025);
        $header = Header::fromComposer($tmpFile, $copyrightRange);

        self::assertSame($copyrightRange, $header->copyrightRange);
        self::assertStringContainsString('2020-2025', $header->__toString());

        unlink($tmpFile);
    }

    public function testFromComposerExtractsCopyrightFromExtra(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
            'authors' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
            ],
            'extra' => [
                'konradmichalik/php-cs-fixer-preset' => [
                    'copyright' => 2020,
                ],
            ],
        ]));

        $header = Header::fromComposer($tmpFile);

        self::assertInstanceOf(CopyrightRange::class, $header->copyrightRange);
        self::assertSame(2020, $header->copyrightRange->from);
        self::assertStringContainsString('2020', $header->__toString());

        unlink($tmpFile);
    }

    public function testFromComposerExplicitCopyrightOverridesExtra(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
            'authors' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
            ],
            'extra' => [
                'konradmichalik/php-cs-fixer-preset' => [
                    'copyright' => 2020,
                ],
            ],
        ]));

        $copyrightRange = CopyrightRange::from(2023, 2025);
        $header = Header::fromComposer($tmpFile, $copyrightRange);

        self::assertSame($copyrightRange, $header->copyrightRange);
        self::assertSame(2023, $header->copyrightRange->from);
        self::assertStringContainsString('2023-2025', $header->__toString());

        unlink($tmpFile);
    }

    public function testFromComposerWithoutCopyrightInExtraReturnsNull(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
        ]));

        $header = Header::fromComposer($tmpFile);

        self::assertNull($header->copyrightRange);

        unlink($tmpFile);
    }
}
