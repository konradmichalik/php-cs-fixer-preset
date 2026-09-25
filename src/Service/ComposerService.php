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

namespace KonradMichalik\PhpCsFixerPreset\Service;

use JsonException;
use KonradMichalik\PhpCsFixerPreset\Package\{Author, CopyrightRange, Type};
use RuntimeException;

use function ctype_digit;
use function explode;
use function file_exists;
use function file_get_contents;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;
use function sprintf;
use function str_contains;

/**
 * ComposerService.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class ComposerService
{
    /**
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     * @throws JsonException
     */
    public static function readComposerJson(string $composerJsonPath = './composer.json'): array
    {
        if (!file_exists($composerJsonPath)) {
            throw new RuntimeException(sprintf('Composer file not found at: %s', $composerJsonPath));
        }

        $contents = file_get_contents($composerJsonPath);
        if (false === $contents) {
            throw new RuntimeException(sprintf('Failed to read composer file at: %s', $composerJsonPath));
        }

        return json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $composerData
     *
     * @throws RuntimeException
     */
    public static function extractPackageType(array $composerData): Type
    {
        $composerType = $composerData['type'] ?? 'library';

        if (!is_string($composerType)) {
            throw new RuntimeException('Composer package type must be a string.');
        }

        return Type::fromComposerType($composerType);
    }

    /**
     * @param array<string, mixed> $composerData
     *
     * @throws RuntimeException
     */
    public static function extractPackageName(array $composerData, Type $packageType): string
    {
        if (Type::TYPO3Extension === $packageType && isset($composerData['extra']['typo3/cms']['extension-key'])) {
            return self::requireNonEmptyString(
                $composerData['extra']['typo3/cms']['extension-key'],
                'TYPO3 extension key must be a non-empty string.',
            );
        }

        $composerName = $composerData['name'] ?? null;

        if (is_string($composerName) && str_contains($composerName, '/')) {
            $composerName = explode('/', $composerName)[1];
        }

        return self::requireNonEmptyString(
            $composerName,
            'Composer package name must be a non-empty string. Pass $packageName explicitly if composer.json has no name.',
        );
    }

    /**
     * @param array<string, mixed> $composerData
     *
     * @return list<Author>
     */
    public static function extractAuthors(array $composerData): array
    {
        if (!isset($composerData['authors']) || !is_array($composerData['authors'])) {
            return [];
        }

        $authors = [];
        foreach ($composerData['authors'] as $authorData) {
            if (!is_array($authorData) || !is_string($authorData['name'] ?? null) || '' === $authorData['name']) {
                continue;
            }

            $email = $authorData['email'] ?? null;

            $authors[] = Author::create($authorData['name'], is_string($email) && '' !== $email ? $email : null);
        }

        return $authors;
    }

    /**
     * @param array<string, mixed> $composerData
     *
     * @throws RuntimeException
     */
    public static function extractCopyrightRange(array $composerData): ?CopyrightRange
    {
        $copyright = $composerData['extra']['konradmichalik/php-cs-fixer-preset']['copyright'] ?? null;

        if (null === $copyright) {
            return null;
        }

        if (is_string($copyright) && ctype_digit($copyright)) {
            $copyright = (int) $copyright;
        }

        if (!is_int($copyright) || $copyright < 1) {
            throw new RuntimeException('Copyright year must be a positive integer.');
        }

        return CopyrightRange::from($copyright);
    }

    /**
     * @throws RuntimeException
     */
    private static function requireNonEmptyString(mixed $value, string $message): string
    {
        if (!is_string($value) || '' === $value) {
            throw new RuntimeException($message);
        }

        return $value;
    }
}
