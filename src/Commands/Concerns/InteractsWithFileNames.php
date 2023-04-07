<?php

namespace RealMrHex\FilamentModular\Commands\Concerns;

use Illuminate\Support\Str;

trait InteractsWithFileNames
{
    /**
     * Ensure arg entered
     *
     * @param string $arg
     * @param string $ask
     *
     * @return string
     */
    private function ensureArg(string $arg, string $ask): string
    {
        return $this->argument($arg) ?? $this->askRequired($ask, $arg);
    }

    /**
     * Ensure option
     *
     * @param string $option
     * @param string $ask
     *
     * @return ?string
     */
    private function ensureOption(string $option, string $ask): ?string
    {
        return $this->option($option) ?? $this->ask($ask);
    }

    /**
     * Simplify String
     *
     * @param string $string
     *
     * @return string
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
     * Simplify resource name
     *
     * @param string $string
     *
     * @return string
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
     * Get class name from string
     *
     * @param string $string
     *
     * @return string
     */
    private function classFromStr(string $string): string
    {
        return Str::of($string)->afterLast('\\')->toString();
    }

    /**
     * Get namespace from string
     *
     * @param string $string
     *
     * @return string
     */
    private function namespaceFromStr(string $string): string
    {
        return Str::of($string)->contains('\\')
            ? Str::of($string)->beforeLast('\\')->toString()
            : '';
    }
}
