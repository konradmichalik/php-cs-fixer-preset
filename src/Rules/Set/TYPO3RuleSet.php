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
use TYPO3\CodingStandards;

use function class_exists;

/**
 * TYPO3RuleSet.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final readonly class TYPO3RuleSet implements Rule
{
    /**
     * @var array<string, mixed>
     */
    private array $rules;

    public function __construct()
    {
        $rules = [];

        if (class_exists(CodingStandards\CsFixerConfig::class)) {
            $rules = CodingStandards\CsFixerConfig::create()->getRules();
        }

        $rules['no_superfluous_phpdoc_tags'] = [
            'allow_mixed' => true,
        ];
        $rules['trailing_comma_in_multiline'] = [
            'elements' => [
                'arguments',
                'arrays',
                'match',
                'parameters',
            ],
        ];

        $this->rules = $rules;
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return $this->rules;
    }
}
