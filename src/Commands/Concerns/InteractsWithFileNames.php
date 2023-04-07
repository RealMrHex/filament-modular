<?php

namespace RealMrHex\FilamentModular\Commands\Concerns;

use Illuminate\Support\Str;

trait InteractsWithFileNames
{
    /**
     * Ensure arg entered.
     */
    private function ensureArg(string $arg, string $ask): string
    {
        return $this->argument($arg) ?? $this->askRequired($ask, $arg);
    }

    /**
     * Ensure option.
     */
    private function ensureOption(string $option, string $ask): ?string
    {
        return $this->option($option) ?? $this->ask($ask);
    }

    /**
     * Simplify String.
     */
    private function simplifyStr(string $string): string
    {
        return Str::of($string)
                  ->trim('/')
                  ->trim('\\')
                  ->trim(' ')
                  ->replace('/', '\\')
                  ->toString();
    }

    /**
     * Simplify resource name.
     */
    private function simplifyResource(string $string): string
    {
        $_ = Str::of($string)
                ->studly()
                ->trim('/')
                ->trim('\\')
                ->trim(' ')
                ->replace('/', '\\')
                ->toString();

        return Str::of($_)->endsWith('Resource') ? $_ : "{$_}Resource";
    }

    /**
     * Get class name from string.
     */
    private function classFromStr(string $string): string
    {
        return Str::of($string)->afterLast('\\')->toString();
    }

    /**
     * Get namespace from string.
     */
    private function namespaceFromStr(string $string): string
    {
        return Str::of($string)->contains('\\')
            ? Str::of($string)->beforeLast('\\')->toString()
            : '';
    }
}
