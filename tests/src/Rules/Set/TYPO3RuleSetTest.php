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

use KonradMichalik\PhpCsFixerPreset\Rules\{Rule, Set\TYPO3RuleSet};
use PHPUnit\Framework\TestCase;
use TYPO3\CodingStandards\CsFixerConfig;

use function in_array;

/**
 * TYPO3RuleSetTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class TYPO3RuleSetTest extends TestCase
{
    public function testImplementsRuleInterface(): void
    {
        $ruleSet = TYPO3RuleSet::create();

        self::assertInstanceOf(Rule::class, $ruleSet);
    }

    public function testCreateReturnsInstance(): void
    {
        $ruleSet = TYPO3RuleSet::create();

        self::assertInstanceOf(TYPO3RuleSet::class, $ruleSet);
    }

    public function testGetReturnsArray(): void
    {
        $ruleSet = TYPO3RuleSet::create();
        $rules = $ruleSet->get();

        self::assertIsArray($rules);
    }

    public function testContainsNoSuperfluousPhpdocTagsRule(): void
    {
        $ruleSet = TYPO3RuleSet::create();
        $rules = $ruleSet->get();

        self::assertArrayHasKey('no_superfluous_phpdoc_tags', $rules);
        self::assertSame(['allow_mixed' => true], $rules['no_superfluous_phpdoc_tags']);
    }

    public function testContainsTrailingCommaInMultilineRule(): void
    {
        $ruleSet = TYPO3RuleSet::create();
        $rules = $ruleSet->get();

        self::assertArrayHasKey('trailing_comma_in_multiline', $rules);
        self::assertSame(
            ['elements' => ['arguments', 'arrays', 'match', 'parameters']],
            $rules['trailing_comma_in_multiline'],
        );
    }

    public function testContainsRulesFromTypo3CodingStandards(): void
    {
        $typo3Rules = CsFixerConfig::create()->getRules();
        $rules = TYPO3RuleSet::create()->get();

        foreach ($typo3Rules as $name => $configuration) {
            if (in_array($name, ['no_superfluous_phpdoc_tags', 'trailing_comma_in_multiline'], true)) {
                continue;
            }

            self::assertArrayHasKey($name, $rules);
            self::assertSame($configuration, $rules[$name]);
        }
    }
}
