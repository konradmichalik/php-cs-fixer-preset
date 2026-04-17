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

namespace KonradMichalik\PhpCsFixerPreset\Tests\Rules\Set;

use KonradMichalik\PhpCsFixerPreset\Rules\{Rule, Set\RuleSet};
use PHPUnit\Framework\TestCase;

/**
 * RuleSetTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class RuleSetTest extends TestCase
{
    public function testImplementsRuleInterface(): void
    {
        $ruleSet = RuleSet::create();

        self::assertInstanceOf(Rule::class, $ruleSet);
    }

    public function testCreateReturnsEmptyRuleSet(): void
    {
        $ruleSet = RuleSet::create();

        self::assertSame([], $ruleSet->get());
    }

    public function testFromArrayCreatesRuleSetWithRules(): void
    {
        $rules = ['strict_types' => true, 'single_quote' => true];
        $ruleSet = RuleSet::fromArray($rules);

        self::assertSame($rules, $ruleSet->get());
    }

    public function testAddMergesRules(): void
    {
        $ruleSet = RuleSet::create();
        $ruleSet->add(['strict_types' => true]);
        $ruleSet->add(['single_quote' => true]);

        self::assertSame(
            ['strict_types' => true, 'single_quote' => true],
            $ruleSet->get(),
        );
    }

    public function testAddDeepMergesArrayRules(): void
    {
        $ruleSet = RuleSet::fromArray([
            'ordered_imports' => [
                'sort_algorithm' => 'alpha',
                'imports_order' => ['class'],
            ],
        ]);

        $ruleSet->add([
            'ordered_imports' => [
                'imports_order' => ['class', 'function', 'const'],
            ],
        ]);

        $expected = [
            'ordered_imports' => [
                'sort_algorithm' => 'alpha',
                'imports_order' => ['class', 'function', 'const'],
            ],
        ];

        self::assertSame($expected, $ruleSet->get());
    }

    public function testAddReturnsFluentInterface(): void
    {
        $ruleSet = RuleSet::create();

        $result = $ruleSet->add(['strict_types' => true]);

        self::assertSame($ruleSet, $result);
    }

    public function testRemoveDeletesRules(): void
    {
        $ruleSet = RuleSet::fromArray([
            'strict_types' => true,
            'single_quote' => true,
            'no_unused_imports' => true,
        ]);

        $ruleSet->remove('single_quote', 'no_unused_imports');

        self::assertSame(['strict_types' => true], $ruleSet->get());
    }

    public function testRemoveIgnoresNonExistentRules(): void
    {
        $ruleSet = RuleSet::fromArray(['strict_types' => true]);

        $ruleSet->remove('non_existent');

        self::assertSame(['strict_types' => true], $ruleSet->get());
    }

    public function testRemoveReturnsFluentInterface(): void
    {
        $ruleSet = RuleSet::create();

        $result = $ruleSet->remove('strict_types');

        self::assertSame($ruleSet, $result);
    }

    public function testFluentChaining(): void
    {
        $ruleSet = RuleSet::create()
            ->add(['strict_types' => true, 'single_quote' => true, 'no_unused_imports' => true])
            ->remove('single_quote')
            ->add(['array_syntax' => ['syntax' => 'short']]);

        $expected = [
            'strict_types' => true,
            'no_unused_imports' => true,
            'array_syntax' => ['syntax' => 'short'],
        ];

        self::assertSame($expected, $ruleSet->get());
    }
}
