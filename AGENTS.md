# AGENTS.md

Guidance for coding agents working in this repository.

## Project overview

A PHP Composer package that provides a preset configuration for PHP CS Fixer. It is intended for personal use by the author and inspired by `eliashaeussler/php-cs-fixer-config`.

- Package: `konradmichalik/php-cs-fixer-preset`, license GPL-3.0-or-later
- PHP `~8.2 || ~8.3 || ~8.4 || ~8.5`, `friendsofphp/php-cs-fixer` `^3.92`, `symfony/finder`, `ext-ctype`
- Optional: `typo3/coding-standards`, required for `TYPO3RuleSet`

## Structure

- `src/Config.php`: main entry point, extends `PhpCsFixer\Config`
- `src/Rules/`: `Rule` interface, `Header`, and `Set/` with `DefaultSet`, `RuleSet`, `TYPO3RuleSet`
- `src/Package/`: metadata classes `Type` (enum), `Author`, `CopyrightRange`, `License`
- `src/Service/ComposerService.php`: reads package metadata from `composer.json`
- `tests/src/`: PHPUnit tests mirroring `src/` (namespace `KonradMichalik\PhpCsFixerPreset\Tests\`)
- `.php-cs-fixer.php`, `phpstan.neon`, `rector.php`, `phpunit.xml`: tool configs in the repository root

How it fits together:

- `Config::create()` builds the default configuration from `DefaultSet`, enables risky rules and configures parallel execution
- `withRule(Rule $rule, bool $merge = true)` adds or merges rules, `withFinder()` takes a `Finder` or a callable, `withConfig()` imports another `ConfigInterface`
- A `Rule` implements `get(): array` and returns PHP CS Fixer rule configuration
- `Header` generates the file header comment, `Header::fromComposer()` reads it from `composer.json`

## Development commands

```bash
composer install
composer lint            # composer normalize --dry-run, editorconfig-cli (ec --git-only), php-cs-fixer --dry-run
composer fix             # same tools, applying fixes
composer sca:php         # phpstan analyse --memory-limit=2G
composer migration       # rector process -c rector.php
```

## Testing

```bash
composer test            # phpunit without coverage
composer test:coverage   # XDEBUG_MODE=coverage phpunit, reports to .build/coverage/
```

Tests live in `tests/src/`. Locally, coverage needs Xdebug. CI runs the tests through the shared reusable workflow `tests-php.yml` on PHP 8.2, 8.3, 8.4 and 8.5, and lint plus static analysis through `cgl.yml` on PHP 8.4. Both are thin wrappers, there is no inline CI logic here.

## Code style and static analysis

- PHP CS Fixer uses this package's own preset (`.php-cs-fixer.php`), with `Header::fromComposer()` for file headers and `DocBlockHeaderFixer` for docblock headers
- PHPStan at level `max` over `src/` and `tests/src/`, with the Symfony and PHPUnit extensions
- Rector with `LevelSetList::UP_TO_PHP_82`, PHP 8.2 target, over `src/` and `tests/`
- EditorConfig is enforced through `ec` (`.editorconfig`)
- Every PHP file declares `declare(strict_types=1);`

## Git workflow

- Commit format: `<type>: <description>`
- Types: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `perf`, `ci`
- No co-author trailers
- One commit per logical change
