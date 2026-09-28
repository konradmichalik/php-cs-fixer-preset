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

namespace KonradMichalik\PhpCsFixerPreset;

use KonradMichalik\PhpCsFixerPreset\Rules\Rule;
use KonradMichalik\PhpCsFixerPreset\Rules\Set\DefaultSet;
use PhpCsFixer\Config\RuleCustomisationPolicyAwareConfigInterface;
use PhpCsFixer\{ConfigInterface, CustomRulesetsAwareConfigInterface, ParallelAwareConfigInterface, Runner};
use PhpCsFixer\Fixer\FixerInterface;
use Symfony\Component\Finder\Finder;

use function array_filter;
use function array_map;
use function array_replace_recursive;
use function array_values;
use function class_exists;
use function in_array;

/**
 * Config.
 *
 * @author Konrad Michalik <hej@konradmichalik.dev>
 * @license GPL-3.0-or-later
 */
final class Config extends \PhpCsFixer\Config
{
    private Finder $finder;

    public function __construct(string $name = 'default')
    {
        parent::__construct($name);
        $this->finder = new Finder();
        $this->setFinder($this->finder);
    }

    public static function create(bool $skipDefaultSet = false): self
    {
        $config = new self();

        if (!$skipDefaultSet) {
            $config->withRule(DefaultSet::create(), false);
        }

        $config->setRiskyAllowed(true);
        $config->finder->name('*.php');
        $config->finder->ignoreDotFiles(false);
        $config->finder->ignoreVCSIgnored(true);

        // Enable parallel execution (PHP-CS-Fixer >= 3.57)
        if (class_exists(Runner\Parallel\ParallelConfig::class)) {
            $config->setParallelConfig(Runner\Parallel\ParallelConfigFactory::detect());
        }

        // Remove this once dependencies declare support for PHP 8.5
        $config->setUnsupportedPhpVersionAllowed(true);

        return $config;
    }

    public function withRule(Rule $rule, bool $merge = true): self
    {
        if ($merge) {
            $rules = array_replace_recursive($this->getRules(), $rule->get());
        } else {
            $rules = $rule->get();
        }

        $this->setRules($rules);

        return $this;
    }

    /**
     * @param Finder|callable(Finder): Finder $finder
     */
    public function withFinder(Finder|callable $finder): self
    {
        if (!$finder instanceof Finder) {
            $finder = $finder($this->finder);
        }

        $this->finder = $finder;
        $this->setFinder($finder);

        return $this;
    }

    /**
     * Replaces this configuration with the given one, including its rules.
     * Rules are not merged, so the DefaultSet is dropped. Custom fixers and
     * rule sets are added to already registered ones.
     */
    public function withConfig(ConfigInterface $config): self
    {
        $finder = $config->getFinder();
        if ($finder instanceof Finder) {
            $this->finder = $finder;
        }

        $this->setFinder($finder);
        $this->setRules($config->getRules());
        $this->setRiskyAllowed($config->getRiskyAllowed());
        $this->registerCustomFixers($this->filterUnregisteredFixers($config->getCustomFixers()));
        $this->setUsingCache($config->getUsingCache());
        $this->setIndent($config->getIndent());
        $this->setLineEnding($config->getLineEnding());
        $this->setFormat($config->getFormat());
        $this->setHideProgress($config->getHideProgress());
        $this->setPhpExecutable($config->getPhpExecutable());

        if (null !== $config->getCacheFile()) {
            $this->setCacheFile($config->getCacheFile());
        }

        if ($config instanceof ParallelAwareConfigInterface) {
            $this->setParallelConfig($config->getParallelConfig());
        }

        if ($config instanceof CustomRulesetsAwareConfigInterface) {
            $this->registerCustomRuleSets($config->getCustomRuleSets());
        }

        if ($config instanceof RuleCustomisationPolicyAwareConfigInterface) {
            $this->setRuleCustomisationPolicy($config->getRuleCustomisationPolicy());
        }

        return $this;
    }

    /**
     * Registering a fixer name twice makes PHP-CS-Fixer fail.
     *
     * @param list<FixerInterface> $fixers
     *
     * @return list<FixerInterface>
     */
    private function filterUnregisteredFixers(array $fixers): array
    {
        $registeredNames = array_map(
            static fn (FixerInterface $fixer): string => $fixer->getName(),
            $this->getCustomFixers(),
        );

        return array_values(array_filter(
            $fixers,
            static fn (FixerInterface $fixer): bool => !in_array($fixer->getName(), $registeredNames, true),
        ));
    }
}
