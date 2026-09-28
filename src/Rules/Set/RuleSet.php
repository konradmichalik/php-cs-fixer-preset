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

use function array_diff_key;
use function array_flip;
use function array_replace;

/**
 * RuleSet.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class RuleSet implements Rule
{
    /**
     * @param array<string, array<string, mixed>|bool> $rules
     */
    public function __construct(
        private array $rules,
    ) {}

    public static function create(): self
    {
        return new self([]);
    }

    /**
     * @param array<string, array<string, mixed>|bool> $rules
     */
    public static function fromArray(array $rules): self
    {
        return new self($rules);
    }

    /**
     * @param array<string, array<string, mixed>|bool> $rules
     */
    public function add(array $rules): self
    {
        $this->rules = array_replace($this->rules, $rules);

        return $this;
    }

    public function remove(string ...$rules): self
    {
        $this->rules = array_diff_key($this->rules, array_flip($rules));

        return $this;
    }

    /**
     * @return array<string, array<string, mixed>|bool>
     */
    public function get(): array
    {
        return $this->rules;
    }
}
