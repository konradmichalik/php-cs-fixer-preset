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
    case SymfonyProject = 'Symfony project';
    case TYPO3Extension = 'TYPO3 CMS extension';
    case TYPO3Project = 'TYPO3 CMS project';

    public static function fromComposerType(string $composerType): self
    {
        return match ($composerType) {
            'composer-plugin' => self::ComposerPlugin,
            'symfony-bundle' => self::SymfonyProject,
            'typo3-cms-extension' => self::TYPO3Extension,
            default => self::ComposerPackage,
        };
    }
}
