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

namespace KonradMichalik\PhpCsFixerPreset\Tests\Service;

use JsonException;
use KonradMichalik\PhpCsFixerPreset\Package\{CopyrightRange, Type};
use KonradMichalik\PhpCsFixerPreset\Service\ComposerService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * ComposerServiceTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class ComposerServiceTest extends TestCase
{
    public function testReadComposerJsonReadsValidFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'name' => 'vendor/package',
            'type' => 'library',
        ]));

        $data = ComposerService::readComposerJson($tmpFile);

        self::assertIsArray($data);
        self::assertSame('vendor/package', $data['name']);
        self::assertSame('library', $data['type']);

        unlink($tmpFile);
    }

    public function testReadComposerJsonThrowsExceptionWhenFileNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Composer file not found at:');

        ComposerService::readComposerJson('/non/existent/composer.json');
    }

    public function testReadComposerJsonThrowsExceptionWhenFileCannotBeRead(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode(['name' => 'vendor/package']));
        chmod($tmpFile, 0000);

        if (false !== @file_get_contents($tmpFile)) {
            chmod($tmpFile, 0644);
            unlink($tmpFile);
            self::markTestSkipped('Cannot reliably remove read permissions on this system');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read composer file at:');

        try {
            @ComposerService::readComposerJson($tmpFile);
        } finally {
            @chmod($tmpFile, 0644);
            @unlink($tmpFile);
        }
    }

    public function testReadComposerJsonThrowsExceptionWhenJsonIsInvalid(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, 'invalid json');

        $this->expectException(JsonException::class);

        try {
            ComposerService::readComposerJson($tmpFile);
        } finally {
            unlink($tmpFile);
        }
    }

    public function testExtractPackageTypeFromComposerPlugin(): void
    {
        $data = ['type' => 'composer-plugin'];
        $type = ComposerService::extractPackageType($data);

        self::assertSame(Type::ComposerPlugin, $type);
    }

    public function testExtractPackageTypeFromSymfonyBundle(): void
    {
        $data = ['type' => 'symfony-bundle'];
        $type = ComposerService::extractPackageType($data);

        self::assertSame(Type::SymfonyProject, $type);
    }

    public function testExtractPackageTypeFromTypo3Extension(): void
    {
        $data = ['type' => 'typo3-cms-extension'];
        $type = ComposerService::extractPackageType($data);

        self::assertSame(Type::TYPO3Extension, $type);
    }

    public function testExtractPackageTypeDefaultsToComposerPackage(): void
    {
        $data = ['type' => 'unknown-type'];
        $type = ComposerService::extractPackageType($data);

        self::assertSame(Type::ComposerPackage, $type);
    }

    public function testExtractPackageTypeWhenMissing(): void
    {
        $data = [];
        $type = ComposerService::extractPackageType($data);

        self::assertSame(Type::ComposerPackage, $type);
    }

    public function testExtractPackageNameFromComposerName(): void
    {
        $data = ['name' => 'vendor/package-name'];
        $name = ComposerService::extractPackageName($data, Type::ComposerPackage);

        self::assertSame('package-name', $name);
    }

    public function testExtractPackageNameFromTypo3ExtensionKey(): void
    {
        $data = [
            'name' => 'vendor/typo3-extension',
            'extra' => [
                'typo3/cms' => [
                    'extension-key' => 'typo3_extension_key',
                ],
            ],
        ];
        $name = ComposerService::extractPackageName($data, Type::TYPO3Extension);

        self::assertSame('typo3_extension_key', $name);
    }

    public function testExtractPackageNameFallsBackToComposerNameForTypo3Extension(): void
    {
        $data = ['name' => 'vendor/typo3-extension'];
        $name = ComposerService::extractPackageName($data, Type::TYPO3Extension);

        self::assertSame('typo3-extension', $name);
    }

    public function testExtractPackageNameHandlesMissingName(): void
    {
        $data = [];
        $name = ComposerService::extractPackageName($data, Type::ComposerPackage);

        self::assertSame('', $name);
    }

    public function testExtractPackageNameHandlesNameWithoutSlash(): void
    {
        $data = ['name' => 'package-only'];
        $name = ComposerService::extractPackageName($data, Type::ComposerPackage);

        self::assertSame('package-only', $name);
    }

    public function testExtractAuthorsFromComposerData(): void
    {
        $data = [
            'authors' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
                ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ],
        ];

        $authors = ComposerService::extractAuthors($data);

        self::assertCount(2, $authors);
        self::assertSame('John Doe', $authors[0]->name);
        self::assertSame('john@example.com', $authors[0]->emailAddress);
        self::assertSame('Jane Smith', $authors[1]->name);
        self::assertSame('jane@example.com', $authors[1]->emailAddress);
    }

    public function testExtractAuthorsReturnsEmptyArrayWhenMissing(): void
    {
        $data = [];
        $authors = ComposerService::extractAuthors($data);

        self::assertSame([], $authors);
    }

    public function testExtractAuthorsReturnsEmptyArrayWhenNotArray(): void
    {
        $data = ['authors' => 'not-an-array'];
        $authors = ComposerService::extractAuthors($data);

        self::assertSame([], $authors);
    }

    public function testExtractAuthorsSkipsInvalidAuthorData(): void
    {
        $data = [
            'authors' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
                'invalid-author',
                ['name' => 'Jane Smith'], // Missing email
                ['email' => 'noreply@example.com'], // Missing name
            ],
        ];

        $authors = ComposerService::extractAuthors($data);

        self::assertCount(1, $authors);
        self::assertSame('John Doe', $authors[0]->name);
    }

    public function testExtractCopyrightRangeFromComposerData(): void
    {
        $data = [
            'extra' => [
                'konradmichalik/php-cs-fixer-preset' => [
                    'copyright' => 2020,
                ],
            ],
        ];

        $copyrightRange = ComposerService::extractCopyrightRange($data);

        self::assertInstanceOf(CopyrightRange::class, $copyrightRange);
        self::assertSame(2020, $copyrightRange->from);
    }

    public function testExtractCopyrightRangeReturnsNullWhenMissing(): void
    {
        $data = [];
        $copyrightRange = ComposerService::extractCopyrightRange($data);

        self::assertNull($copyrightRange);
    }

    public function testExtractCopyrightRangeReturnsNullWhenNotInteger(): void
    {
        $data = [
            'extra' => [
                'konradmichalik/php-cs-fixer-preset' => [
                    'copyright' => '2020',
                ],
            ],
        ];

        $copyrightRange = ComposerService::extractCopyrightRange($data);

        self::assertNull($copyrightRange);
    }

    public function testExtractCopyrightRangeReturnsNullWhenExtraIsMissing(): void
    {
        $data = ['name' => 'vendor/package'];
        $copyrightRange = ComposerService::extractCopyrightRange($data);

        self::assertNull($copyrightRange);
    }
}
