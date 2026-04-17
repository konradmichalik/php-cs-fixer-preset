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

use function explode;
use function file_exists;
use function file_get_contents;
use function is_array;
use function is_int;
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
     */
    public static function extractPackageType(array $composerData): Type
    {
        $composerType = $composerData['type'] ?? 'library';

        return Type::fromComposerType($composerType);
    }

    /**
     * @param array<string, mixed> $composerData
     */
    public static function extractPackageName(array $composerData, Type $packageType): string
    {
        if (Type::TYPO3Extension === $packageType && isset($composerData['extra']['typo3/cms']['extension-key'])) {
            return $composerData['extra']['typo3/cms']['extension-key'];
        }

        $composerName = $composerData['name'] ?? '';

        return str_contains((string) $composerName, '/')
            ? explode('/', (string) $composerName)[1]
            : $composerName;
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
            if (!is_array($authorData)) {
                continue;
            }

            $name = $authorData['name'] ?? null;
            $email = $authorData['email'] ?? null;

            if (null === $name || null === $email) {
                continue;
            }

            $authors[] = Author::create($name, $email);
        }

        return $authors;
    }

    /**
     * @param array<string, mixed> $composerData
     */
    public static function extractCopyrightRange(array $composerData): ?CopyrightRange
    {
        $copyright = $composerData['extra']['konradmichalik/php-cs-fixer-preset']['copyright'] ?? null;

        if (!is_int($copyright)) {
            return null;
        }

        return CopyrightRange::from($copyright);
    }
}
