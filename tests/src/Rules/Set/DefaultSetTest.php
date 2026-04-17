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

use KonradMichalik\PhpCsFixerPreset\Rules\{Rule, Set\DefaultSet};
use PHPUnit\Framework\TestCase;

/**
 * DefaultSetTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class DefaultSetTest extends TestCase
{
    private DefaultSet $defaultSet;

    protected function setUp(): void
    {
        $this->defaultSet = DefaultSet::create();
    }

    public function testImplementsRuleInterface(): void
    {
        self::assertInstanceOf(Rule::class, $this->defaultSet);
    }

    public function testCreateReturnsDefaultSetInstance(): void
    {
        self::assertInstanceOf(DefaultSet::class, $this->defaultSet);
    }

    public function testGetReturnsArray(): void
    {
        $rules = $this->defaultSet->get();

        self::assertIsArray($rules);
        self::assertNotEmpty($rules);
    }

    public function testIncludesPERCSRuleSet(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('@PER-CS', $rules);
        self::assertTrue($rules['@PER-CS']);
    }

    public function testIncludesPSR12RuleSet(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('@PSR12', $rules);
        self::assertTrue($rules['@PSR12']);
    }

    public function testIncludesSymfonyRuleSet(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('@Symfony', $rules);
        self::assertTrue($rules['@Symfony']);
    }

    public function testIncludesSymfonyRiskyRuleSet(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('@Symfony:risky', $rules);
        self::assertTrue($rules['@Symfony:risky']);
    }

    public function testDisablesSingleImportPerStatement(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('single_import_per_statement', $rules);
        self::assertFalse($rules['single_import_per_statement']);
    }

    public function testEnablesGlobalNamespaceImport(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('global_namespace_import', $rules);
        self::assertIsArray($rules['global_namespace_import']);
        self::assertTrue($rules['global_namespace_import']['import_classes']);
        self::assertTrue($rules['global_namespace_import']['import_functions']);
    }

    public function testConfiguresNoSuperfluousPhpdocTags(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('no_superfluous_phpdoc_tags', $rules);
        self::assertIsArray($rules['no_superfluous_phpdoc_tags']);
        self::assertTrue($rules['no_superfluous_phpdoc_tags']['allow_mixed']);
    }

    public function testConfiguresOrderedClassElements(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('ordered_class_elements', $rules);
        self::assertIsArray($rules['ordered_class_elements']);
        self::assertArrayHasKey('order', $rules['ordered_class_elements']);

        $order = $rules['ordered_class_elements']['order'];
        self::assertIsArray($order);
        self::assertContains('use_trait', $order);
        self::assertContains('construct', $order);
        self::assertContains('method_public', $order);
    }

    public function testEnablesSingleLineEmptyBody(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('single_line_empty_body', $rules);
        self::assertTrue($rules['single_line_empty_body']);
    }

    public function testConfiguresTrailingCommaInMultiline(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('trailing_comma_in_multiline', $rules);
        self::assertIsArray($rules['trailing_comma_in_multiline']);
        self::assertArrayHasKey('elements', $rules['trailing_comma_in_multiline']);

        $elements = $rules['trailing_comma_in_multiline']['elements'];
        self::assertIsArray($elements);
        self::assertContains('arguments', $elements);
        self::assertContains('arrays', $elements);
        self::assertContains('match', $elements);
        self::assertContains('parameters', $elements);
    }

    public function testEnablesDeclareStrictTypes(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('declare_strict_types', $rules);
        self::assertTrue($rules['declare_strict_types']);
    }

    public function testEnablesNoUnusedImports(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('no_unused_imports', $rules);
        self::assertTrue($rules['no_unused_imports']);
    }

    public function testEnablesGroupImport(): void
    {
        $rules = $this->defaultSet->get();

        self::assertArrayHasKey('group_import', $rules);
        self::assertTrue($rules['group_import']);
    }

    public function testOrderedClassElementsHasCorrectOrder(): void
    {
        $rules = $this->defaultSet->get();
        $order = $rules['ordered_class_elements']['order'];

        $expectedOrder = [
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
        ];

        self::assertSame($expectedOrder, $order);
    }
}
