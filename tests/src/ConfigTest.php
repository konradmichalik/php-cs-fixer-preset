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

use FilesystemIterator;
use KonradMichalik\PhpCsFixerPreset\{Config, Rules\Rule, Rules\Set\RuleSet};
use KonradMichalik\PhpDocBlockHeaderFixer\Rules\DocBlockHeaderFixer;
use PhpCsFixer\RuleSet\AbstractRuleSetDefinition;
use PhpCsFixer\Runner\Parallel\ParallelConfig;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

use function count;
use function strlen;

/**
 * ConfigTest.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class ConfigTest extends TestCase
{
    public function testCreateEnablesRiskyRules(): void
    {
        $config = Config::create();

        self::assertTrue($config->getRiskyAllowed());
    }

    public function testCreateIncludesDefaultRules(): void
    {
        $config = Config::create();
        $rules = $config->getRules();

        self::assertArrayHasKey('@PER-CS3x0', $rules);
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
        self::assertArrayHasKey('@PER-CS3x0', $newRules);
        self::assertCount(count($existingRules) + 1, $newRules);
    }

    public function testWithRuleReplacesListOptionsOfExistingRule(): void
    {
        $config = Config::create()->withRule(RuleSet::fromArray([
            'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        ]));

        self::assertSame(
            ['elements' => ['arrays']],
            $config->getRules()['trailing_comma_in_multiline'],
        );
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
        self::assertArrayNotHasKey('@PER-CS3x0', $rules);
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

    public function testWithConfigImportsRemainingSettings(): void
    {
        $customFixer = new DocBlockHeaderFixer();
        $parallelConfig = new ParallelConfig(2, 5);
        $sourceConfig = (new \PhpCsFixer\Config())
            ->registerCustomFixers([$customFixer])
            ->setCacheFile('/tmp/custom.cache')
            ->setUsingCache(false)
            ->setIndent("\t")
            ->setLineEnding("\r\n")
            ->setFormat('json')
            ->setHideProgress(true)
            ->setPhpExecutable('/usr/bin/php')
            ->setParallelConfig($parallelConfig);

        $targetConfig = Config::create()->withConfig($sourceConfig);

        self::assertSame([$customFixer], $targetConfig->getCustomFixers());
        self::assertSame('/tmp/custom.cache', $targetConfig->getCacheFile());
        self::assertFalse($targetConfig->getUsingCache());
        self::assertSame("\t", $targetConfig->getIndent());
        self::assertSame("\r\n", $targetConfig->getLineEnding());
        self::assertSame('json', $targetConfig->getFormat());
        self::assertTrue($targetConfig->getHideProgress());
        self::assertSame('/usr/bin/php', $targetConfig->getPhpExecutable());
        self::assertSame($parallelConfig, $targetConfig->getParallelConfig());
    }

    public function testWithConfigDoesNotRegisterCustomFixerTwice(): void
    {
        $sourceConfig = (new \PhpCsFixer\Config())->registerCustomFixers([new DocBlockHeaderFixer()]);

        $targetConfig = Config::create()
            ->withConfig($sourceConfig)
            ->withConfig($sourceConfig);

        self::assertCount(1, $targetConfig->getCustomFixers());
    }

    public function testWithConfigImportsCustomRuleSets(): void
    {
        $ruleSet = new class extends AbstractRuleSetDefinition {
            public function getName(): string
            {
                return '@Vendor/custom';
            }

            public function getRules(): array
            {
                return ['single_quote' => true];
            }

            public function getDescription(): string
            {
                return 'Custom rule set.';
            }
        };
        $sourceConfig = (new \PhpCsFixer\Config())->registerCustomRuleSets([$ruleSet]);

        $targetConfig = Config::create()->withConfig($sourceConfig);

        self::assertSame([$ruleSet], $targetConfig->getCustomRuleSets());
    }

    public function testWithFinderAfterWithConfigReceivesImportedFinder(): void
    {
        $sourceFinder = (new Finder())->in(__DIR__);
        $sourceConfig = (new \PhpCsFixer\Config())->setFinder($sourceFinder);

        $receivedFinder = null;
        Config::create()
            ->withConfig($sourceConfig)
            ->withFinder(static function (Finder $finder) use (&$receivedFinder): Finder {
                $receivedFinder = $finder;

                return $finder;
            });

        self::assertSame($sourceFinder, $receivedFinder);
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

    public function testCreateExcludesDependencyDirectoriesWithoutGitignore(): void
    {
        $root = sys_get_temp_dir().'/php-cs-fixer-preset-'.bin2hex(random_bytes(4));
        foreach (['src/nested/vendor', 'vendor/acme', 'node_modules/acme', 'directory.php'] as $directory) {
            mkdir($root.'/'.$directory, 0o777, true);
        }
        foreach (['src/Foo.php', 'src/nested/vendor/Qux.php', 'vendor/acme/Bar.php', 'node_modules/acme/Baz.php'] as $path) {
            file_put_contents($root.'/'.$path, '<?php');
        }

        try {
            $files = [];
            $finder = Config::create()
                ->withFinder(static fn (Finder $finder): Finder => $finder->in($root))
                ->getFinder();
            foreach ($finder as $file) {
                $files[] = substr($file->getPathname(), strlen($root) + 1);
            }
        } finally {
            $entries = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
            );
            /** @var SplFileInfo $entry */
            foreach ($entries as $entry) {
                $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
            }
            rmdir($root);
        }

        self::assertSame(['src/Foo.php'], $files);
    }
}
