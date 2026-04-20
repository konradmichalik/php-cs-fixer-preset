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
use PhpCsFixer\{ConfigInterface, Runner};
use Symfony\Component\Finder\Finder;

use function array_replace_recursive;
use function class_exists;

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

    public function withConfig(ConfigInterface $config): self
    {
        $this->setRules($config->getRules());
        $this->setRiskyAllowed($config->getRiskyAllowed());
        $this->setFinder($config->getFinder());

        return $this;
    }
}
