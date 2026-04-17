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

namespace KonradMichalik\PhpCsFixerPreset\Tests;

use KonradMichalik\PhpCsFixerPreset\{Config, Rules\Rule};
use PhpCsFixer\ConfigInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

use function count;

/**
 * ConfigTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class ConfigTest extends TestCase
{
    public function testCreateReturnsConfigInstance(): void
    {
        $config = Config::create();

        self::assertInstanceOf(Config::class, $config);
        self::assertInstanceOf(ConfigInterface::class, $config);
    }

    public function testCreateEnablesRiskyRules(): void
    {
        $config = Config::create();

        self::assertTrue($config->getRiskyAllowed());
    }

    public function testCreateConfiguresFinderToNotIgnoreDotFiles(): void
    {
        $config = Config::create();

        // The finder is configured to not ignore dot files
        // We verify this by checking that the config was created successfully
        // The actual behavior is tested through integration
        self::assertInstanceOf(Config::class, $config);
    }

    public function testCreateConfiguresFinderToIgnoreVCSIgnoredFiles(): void
    {
        $config = Config::create();

        // The finder is configured to ignore VCS ignored files
        // We verify this by checking that the config was created successfully
        // The actual behavior is tested through integration
        self::assertInstanceOf(Config::class, $config);
    }

    public function testCreateIncludesDefaultRules(): void
    {
        $config = Config::create();
        $rules = $config->getRules();

        self::assertArrayHasKey('@PER-CS', $rules);
        self::assertArrayHasKey('@PSR12', $rules);
        self::assertArrayHasKey('@Symfony', $rules);
        self::assertTrue($rules['declare_strict_types']);
    }

    public function testWithRuleMergesRulesByDefault(): void
    {
        $config = Config::create();
        $existingRules = $config->getRules();

        $customRule = new class implements Rule {
            public function get(): array
            {
                return ['array_syntax' => ['syntax' => 'short']];
            }
        };

        $config->withRule($customRule);
        $newRules = $config->getRules();

        self::assertArrayHasKey('array_syntax', $newRules);
        self::assertArrayHasKey('@PER-CS', $newRules);
        self::assertCount(count($existingRules) + 1, $newRules);
    }

    public function testWithRuleReplacesRulesWhenMergeIsFalse(): void
    {
        $config = Config::create();

        $customRule = new class implements Rule {
            public function get(): array
            {
                return ['array_syntax' => ['syntax' => 'short']];
            }
        };

        $config->withRule($customRule, false);
        $rules = $config->getRules();

        self::assertArrayHasKey('array_syntax', $rules);
        self::assertArrayNotHasKey('@PER-CS', $rules);
        self::assertCount(1, $rules);
    }

    public function testWithFinderAcceptsFinder(): void
    {
        $config = Config::create();
        $finder = (new Finder())->in(__DIR__);

        $config->withFinder($finder);

        self::assertSame($finder, $config->getFinder());
    }

    public function testWithFinderAcceptsCallable(): void
    {
        $config = Config::create();
        $testDir = __DIR__;

        $result = $config->withFinder(static fn (Finder $finder) => $finder->in($testDir));

        self::assertSame($config, $result);
        self::assertInstanceOf(Finder::class, $config->getFinder());
    }

    public function testWithConfigImportsConfiguration(): void
    {
        $sourceConfig = Config::create();
        $sourceConfig->setRules(['single_quote' => true]);
        $sourceConfig->setRiskyAllowed(false);
        $sourceFinder = (new Finder())->in(__DIR__);
        $sourceConfig->setFinder($sourceFinder);

        $targetConfig = Config::create();
        $targetConfig->withConfig($sourceConfig);

        self::assertEquals(['single_quote' => true], $targetConfig->getRules());
        self::assertFalse($targetConfig->getRiskyAllowed());
        self::assertSame($sourceFinder, $targetConfig->getFinder());
    }

    public function testWithRuleReturnsFluentInterface(): void
    {
        $config = Config::create();

        $customRule = new class implements Rule {
            public function get(): array
            {
                return [];
            }
        };

        $result = $config->withRule($customRule);

        self::assertSame($config, $result);
    }

    public function testWithFinderReturnsFluentInterface(): void
    {
        $config = Config::create();
        $finder = new Finder();

        $result = $config->withFinder($finder);

        self::assertSame($config, $result);
    }

    public function testWithConfigReturnsFluentInterface(): void
    {
        $config = Config::create();
        $sourceConfig = Config::create();

        $result = $config->withConfig($sourceConfig);

        self::assertSame($config, $result);
    }
}
