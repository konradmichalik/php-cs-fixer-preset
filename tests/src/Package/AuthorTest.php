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

use KonradMichalik\PhpCsFixerPreset\Package\Author;
use PHPUnit\Framework\TestCase;
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
}
