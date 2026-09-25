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

namespace KonradMichalik\PhpCsFixerPreset\Package;

use function in_array;

/**
 * Type.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
enum Type: string
{
    case ComposerPackage = 'Composer package';
    case ComposerPlugin = 'Composer plugin';
    case SymfonyBundle = 'Symfony bundle';
    case SymfonyProject = 'Symfony project';
    case TYPO3Extension = 'TYPO3 CMS extension';
    case TYPO3Project = 'TYPO3 CMS project';

    /**
     * @param list<string> $requiredPackages
     */
    public static function fromComposerType(string $composerType, array $requiredPackages = []): self
    {
        return match (true) {
            'composer-plugin' === $composerType => self::ComposerPlugin,
            'symfony-bundle' === $composerType => self::SymfonyBundle,
            'typo3-cms-extension' === $composerType => self::TYPO3Extension,
            'project' === $composerType && in_array('typo3/cms-core', $requiredPackages, true) => self::TYPO3Project,
            'project' === $composerType && in_array('symfony/framework-bundle', $requiredPackages, true) => self::SymfonyProject,
            default => self::ComposerPackage,
        };
    }
}
