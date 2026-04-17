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

use KonradMichalik\PhpCsFixerPreset\Package\{Author, CopyrightRange, License, Type};
use KonradMichalik\PhpCsFixerPreset\Rules\{Header, Rule};
use PHPUnit\Framework\TestCase;

/**
 * HeaderTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class HeaderTest extends TestCase
{
    public function testImplementsRuleInterface(): void
    {
        $header = Header::create('test/package', Type::ComposerPackage);

        self::assertInstanceOf(Rule::class, $header);
    }

    public function testCreateWithSingleAuthor(): void
    {
        $author = Author::create('John Doe', 'john@example.com');
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            $author,
        );

        self::assertSame('test/package', $header->packageName);
        self::assertSame(Type::ComposerPackage, $header->packageType);
        self::assertCount(1, $header->packageAuthors);
        self::assertSame($author, $header->packageAuthors[0]);
    }

    public function testCreateWithMultipleAuthors(): void
    {
        $authors = [
            Author::create('John Doe', 'john@example.com'),
            Author::create('Jane Smith', 'jane@example.com'),
        ];

        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            $authors,
        );

        self::assertCount(2, $header->packageAuthors);
        self::assertSame($authors, $header->packageAuthors);
    }

    public function testCreateWithoutAuthors(): void
    {
        $header = Header::create('test/package', Type::ComposerPackage);

        self::assertCount(0, $header->packageAuthors);
    }

    public function testCreateWithCopyrightRange(): void
    {
        $copyrightRange = CopyrightRange::from(2020);
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            [],
            $copyrightRange,
        );

        self::assertSame($copyrightRange, $header->copyrightRange);
    }

    public function testGetReturnsCorrectStructure(): void
    {
        $header = Header::create('test/package', Type::ComposerPackage);
        $rules = $header->get();

        self::assertIsArray($rules);
        self::assertArrayHasKey('header_comment', $rules);
        self::assertIsArray($rules['header_comment']);
        self::assertArrayHasKey('header', $rules['header_comment']);
        self::assertArrayHasKey('comment_type', $rules['header_comment']);
        self::assertArrayHasKey('location', $rules['header_comment']);
        self::assertArrayHasKey('separate', $rules['header_comment']);
    }

    public function testGetConfiguresCommentType(): void
    {
        $header = Header::create('test/package', Type::ComposerPackage);
        $rules = $header->get();

        self::assertSame('comment', $rules['header_comment']['comment_type']);
    }

    public function testGetConfiguresLocation(): void
    {
        $header = Header::create('test/package', Type::ComposerPackage);
        $rules = $header->get();

        self::assertSame('after_declare_strict', $rules['header_comment']['location']);
    }

    public function testGetConfiguresSeparation(): void
    {
        $header = Header::create('test/package', Type::ComposerPackage);
        $rules = $header->get();

        self::assertSame('both', $rules['header_comment']['separate']);
    }

    public function testToStringWithoutAuthors(): void
    {
        $header = Header::create('test/package', Type::ComposerPackage);
        $result = $header->__toString();

        self::assertStringContainsString('This file is part of the "test/package" Composer package.', $result);
        self::assertStringContainsString('For the full copyright and license information', $result);
        self::assertStringNotContainsString('(c)', $result);
    }

    public function testToStringWithSingleAuthor(): void
    {
        $author = Author::create('John Doe', 'john@example.com');
        $copyrightRange = CopyrightRange::from(2025);
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            $author,
            $copyrightRange,
        );

        $result = $header->__toString();

        self::assertStringContainsString('This file is part of the "test/package" Composer package.', $result);
        self::assertStringContainsString('(c) 2025', $result);
        self::assertStringContainsString('John Doe <john@example.com>', $result);
        self::assertStringContainsString('For the full copyright and license information', $result);
    }

    public function testToStringWithMultipleAuthors(): void
    {
        $authors = [
            Author::create('John Doe', 'john@example.com'),
            Author::create('Jane Smith', 'jane@example.com'),
        ];
        $copyrightRange = CopyrightRange::from(2025);
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            $authors,
            $copyrightRange,
        );

        $result = $header->__toString();

        self::assertStringContainsString('John Doe <john@example.com>', $result);
        self::assertStringContainsString('Jane Smith <jane@example.com>', $result);
    }

    public function testToStringWithDifferentPackageTypes(): void
    {
        $header = Header::create('test/plugin', Type::ComposerPlugin);
        $result = $header->__toString();

        self::assertStringContainsString('This file is part of the "test/plugin" Composer plugin.', $result);
    }

    public function testToStringWithCopyrightRange(): void
    {
        $author = Author::create('John Doe', 'john@example.com');
        $copyrightRange = CopyrightRange::from(2020, 2025);
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            $author,
            $copyrightRange,
        );

        $result = $header->__toString();

        self::assertStringContainsString('(c) 2020-2025 John Doe <john@example.com>', $result);
    }

    public function testToStringWithAuthorsButNoCopyrightRange(): void
    {
        $author = Author::create('John Doe', 'john@example.com');
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            $author,
        );

        $result = $header->__toString();

        self::assertStringContainsString('This file is part of the "test/package" Composer package.', $result);
        self::assertStringContainsString('(c) John Doe <john@example.com>', $result);
        self::assertStringNotContainsString('2025', $result);
        self::assertStringContainsString('For the full copyright and license information', $result);
    }

    public function testToStringWithMultipleAuthorsButNoCopyrightRange(): void
    {
        $authors = [
            Author::create('John Doe', 'john@example.com'),
            Author::create('Jane Smith', 'jane@example.com'),
        ];
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            $authors,
        );

        $result = $header->__toString();

        self::assertStringContainsString('(c) John Doe <john@example.com>', $result);
        self::assertStringContainsString('(c) Jane Smith <jane@example.com>', $result);
        self::assertStringNotContainsString('2025', $result);
    }

    public function testToStringWithGpl3License(): void
    {
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            [],
            null,
            License::GPL3OrLater,
        );

        $result = $header->__toString();

        self::assertStringContainsString('either version 3 of the License', $result);
        self::assertStringContainsString('GNU General Public License', $result);
        self::assertStringNotContainsString('please view the LICENSE', $result);
    }

    public function testToStringWithGpl2License(): void
    {
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            [],
            null,
            License::GPL2OrLater,
        );

        $result = $header->__toString();

        self::assertStringContainsString('either version 2 of the License', $result);
        self::assertStringNotContainsString('please view the LICENSE', $result);
    }

    public function testToStringWithProprietaryLicenseFallsBackToDefault(): void
    {
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            [],
            null,
            License::Proprietary,
        );

        $result = $header->__toString();

        self::assertStringContainsString('please view the LICENSE', $result);
    }

    public function testToStringWithoutLicenseUsesDefaultText(): void
    {
        $header = Header::create('test/package', Type::ComposerPackage);

        $result = $header->__toString();

        self::assertStringContainsString('please view the LICENSE', $result);
    }

    public function testCreateWithLicenseParameter(): void
    {
        $header = Header::create(
            'test/package',
            Type::ComposerPackage,
            [],
            null,
            License::GPL3OrLater,
        );

        self::assertSame(License::GPL3OrLater, $header->license);
    }
}
