<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use function preg_match;
use function sprintf;
use Webmozart\Glob\Glob;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SourceFilter
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(
                Registry::get()->source(),
            );
        }

        return self::$instance;
    }

    public function __construct(private Source $source)
    {
    }

    public function includes(string $path): bool
    {
        $includedFile = false;

        foreach ($this->source->includeFiles() as $file) {
            if ($file->path() === $path) {
                $includedFile = true;
            }
        }
        $includePatterns = $this->toGlobs($this->source->includeDirectories());
        $excludePatterns = $this->toGlobs($this->source->excludeDirectories());

        if ($includedFile === false) {
            foreach ($includePatterns as $pattern) {
                if (Glob::match($path, $pattern)) {
                    return false;
                }
            }
        }

        foreach ($this->source->excludeFiles() as $file) {
            if ($file->path() === $path) {
                return false;
            }
        }

        foreach ($excludePatterns as $pattern) {
            if (Glob::match($path, $pattern)) {
                return false;
            }
        }

        return true;
    }

    private function toGlobs(FilterDirectoryCollection $dirs): array
    {
        $globs = [];

        foreach ($dirs as $dir) {
            $globs[] = sprintf('%s%s/*%s', $dir->path(), $dir->prefix(), $dir->suffix());
        }

        return $globs;
    }

    private function matches(array $includePatterns, string $path): bool
    {
        $matched = false;

        foreach ($includePatterns as $includePattern) {
            if (preg_match('~^/home/daniel/www/phpunit/phpunit/[^/]*[^/]*\.php$~', $path)) {
                $matched = true;

                break;
            }
        }

        return $matched;
    }
}
