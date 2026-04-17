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

namespace KonradMichalik\PhpCsFixerPreset\Rules\Set;

use KonradMichalik\PhpCsFixerPreset\Rules\Rule;

/**
 * DefaultSet.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final readonly class DefaultSet implements Rule
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return [
            '@PER-CS' => true,
            '@PSR12' => true,
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'single_import_per_statement' => false,
            'global_namespace_import' => [
                'import_classes' => true,
                'import_functions' => true,
            ],
            'no_superfluous_phpdoc_tags' => [
                'allow_mixed' => true,
            ],
            'ordered_class_elements' => [
                'order' => [
                    'use_trait',
                    'case',
                    'constant_public',
                    'constant_protected',
                    'constant_private',
                    'property_public',
                    'property_protected',
                    'property_private',
                    'construct',
                    'destruct',
                    'magic',
                    'phpunit',
                    'method_public',
                    'method_protected',
                    'method_private',
                ],
            ],
            'single_line_empty_body' => true,
            'trailing_comma_in_multiline' => [
                'elements' => [
                    'arguments',
                    'arrays',
                    'match',
                    'parameters',
                ],
            ],
            'declare_strict_types' => true,
            'no_unused_imports' => true,
            'group_import' => true,
        ];
    }
}
