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

use JsonException;
use KonradMichalik\PhpCsFixerPreset\Package\Author;
use KonradMichalik\PhpCsFixerPreset\Service\ComposerService;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Stringable;

/**
 * AuthorTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class AuthorTest extends TestCase
{
    public function testImplementsStringable(): void
    {
        $author = Author::create('John Doe', 'john@example.com');

        self::assertInstanceOf(Stringable::class, $author);
    }

    public function testCreateReturnsAuthorInstance(): void
    {
        $author = Author::create('John Doe', 'john@example.com');

        self::assertInstanceOf(Author::class, $author);
    }

    public function testStoresNameCorrectly(): void
    {
        $author = Author::create('John Doe', 'john@example.com');

        self::assertSame('John Doe', $author->name);
    }

    public function testStoresEmailAddressCorrectly(): void
    {
        $author = Author::create('John Doe', 'john@example.com');

        self::assertSame('john@example.com', $author->emailAddress);
    }

    public function testToStringReturnsCorrectFormat(): void
    {
        $author = Author::create('John Doe', 'john@example.com');

        self::assertSame('John Doe <john@example.com>', $author->__toString());
    }

    public function testToStringCanBeCastToString(): void
    {
        $author = Author::create('John Doe', 'john@example.com');

        self::assertSame('John Doe <john@example.com>', (string) $author);
    }

    public function testWorksWithSpecialCharactersInName(): void
    {
        $author = Author::create('Jöhn Döe-Smith', 'john@example.com');

        self::assertSame('Jöhn Döe-Smith', $author->name);
        self::assertSame('Jöhn Döe-Smith <john@example.com>', (string) $author);
    }

    public function testWorksWithComplexEmailAddresses(): void
    {
        $author = Author::create('John Doe', 'john.doe+test@sub.example.com');

        self::assertSame('john.doe+test@sub.example.com', $author->emailAddress);
        self::assertSame('John Doe <john.doe+test@sub.example.com>', (string) $author);
    }

    public function testFromComposerReadsAuthorsFromFile(): void
    {
        $composerData = ComposerService::readComposerJson(__DIR__.'/../../../composer.json');
        $authors = ComposerService::extractAuthors($composerData);

        self::assertIsArray($authors);
        self::assertNotEmpty($authors);
        self::assertContainsOnlyInstancesOf(Author::class, $authors);
    }

    public function testFromComposerReturnsEmptyArrayWhenNoAuthors(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, '{"name":"test/package"}');

        $composerData = ComposerService::readComposerJson($tmpFile);
        $authors = ComposerService::extractAuthors($composerData);

        self::assertIsArray($authors);
        self::assertEmpty($authors);

        unlink($tmpFile);
    }

    public function testFromComposerThrowsExceptionWhenFileNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Composer file not found at: /non/existent/composer.json');

        ComposerService::readComposerJson('/non/existent/composer.json');
    }

    public function testFromComposerThrowsExceptionWhenFileCannotBeRead(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, '{"name":"test"}');
        chmod($tmpFile, 0000);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read composer file at:');

        try {
            @ComposerService::readComposerJson($tmpFile);
        } finally {
            chmod($tmpFile, 0644);
            unlink($tmpFile);
        }
    }

    public function testFromComposerThrowsExceptionWhenJsonIsInvalid(): void
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

    public function testFromComposerParsesMultipleAuthors(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'authors' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
                ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ],
        ]));

        $composerData = ComposerService::readComposerJson($tmpFile);
        $authors = ComposerService::extractAuthors($composerData);

        self::assertCount(2, $authors);
        self::assertSame('John Doe', $authors[0]->name);
        self::assertSame('john@example.com', $authors[0]->emailAddress);
        self::assertSame('Jane Smith', $authors[1]->name);
        self::assertSame('jane@example.com', $authors[1]->emailAddress);

        unlink($tmpFile);
    }

    public function testFromComposerSkipsInvalidAuthors(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'composer');
        file_put_contents($tmpFile, json_encode([
            'authors' => [
                ['name' => 'John Doe', 'email' => 'john@example.com'],
                ['name' => 'No Email'],
                ['email' => 'noname@example.com'],
                'invalid',
                ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ],
        ]));

        $composerData = ComposerService::readComposerJson($tmpFile);
        $authors = ComposerService::extractAuthors($composerData);

        self::assertCount(2, $authors);
        self::assertSame('John Doe', $authors[0]->name);
        self::assertSame('Jane Smith', $authors[1]->name);

        unlink($tmpFile);
    }
}
