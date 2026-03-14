<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\OS\Path;

use App\Domain\Shared\Exception\CriticalException;
use App\Domain\Shared\Ports\OS\Path\NormalizePathPort;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

final readonly class NormalizePath implements NormalizePathPort
{
    public function execute(string $path): string
    {
        return match (PHP_OS_FAMILY) {
            'Linux' => $this->executeLinux($path),
            default => throw new CriticalException("Unsupported OS: " . PHP_OS_FAMILY)
        };
    }

    protected function executeLinux(string $path): string
    {
        /**
         * Expand environment variables $VAR
         */
        $path = self::expandEnvVariables($path);


        /**
         * Expand ~ and ~user
         */
        $path = self::expandTilde($path);


        /**
         * Normalize multiple slashes
         */
        $path = self::normalizeSlashes($path);


        /**
         * Resolve '.' and '..' components
         */
        return self::resolveDots($path);
    }


    /**
     * Expands environment variables $VAR in the path.
     *
     * @param string $path Input path
     *
     * @return string Path with expanded environment variables
     */
    private static function expandEnvVariables(string $path): string
    {
        /**
         * Use preg_replace_callback to find $VAR patterns
         */
        return preg_replace_callback('/\$(\w+)/', function ($matches) {

            /**
             * Get environment variable value
             */
            return getenv($matches[1]) ?: '';

        }, $path);
    }


    /**
     * Expands ~ or ~user to home directory.
     *
     * @param string $path Input path
     *
     * @return string Path with expanded tilde
     *
     * @throws InvalidArgumentException If the specified user does not exist
     * @throws RuntimeException If HOME env variable is not set
     * @throws UnexpectedValueException If HOME env variable is not string
     */
    private static function expandTilde(string $path): string
    {
        /**
         * Check if path starts with ~
         */
        if (!preg_match('#^~([^/]*)#', $path, $matches)) {
            return $path;
        }


        $user = $matches[1];

        /**
         * If ~ without username, use current user's home
         */
        if ($user === '') {
            $home = getenv('HOME') ?: '';

            /**
             * Check HOME exists and is string
             */
            if (getenv('HOME') === false) {
                throw new RuntimeException("Cannot get HOME environment variable");
            }
            if (!is_string(getenv('HOME'))) {
                throw new UnexpectedValueException("HOME environment variable must be a string");
            }
        } else {

            /**
             * Check if POSIX functions are available
             */
            if (!function_exists('posix_getpwnam')) {
                throw new InvalidArgumentException("Cannot expand ~user: posix functions not available");
            }


            /**
             * Get home directory of the specified user
             */
            $pw = posix_getpwnam($user);
            if ($pw === false || empty($pw['dir'])) {
                throw new InvalidArgumentException("User '$user' not found");
            }
            $home = $pw['dir'];
        }


        /**
         * Replace ~ or ~user with home directory
         */
        return $home . substr($path, strlen($matches[0]));
    }


    /**
     * Normalizes multiple consecutive slashes.
     *
     * @param string $path Input path
     *
     * @return string Path with normalized slashes
     */
    private static function normalizeSlashes(string $path): string
    {
        /**
         * Replace sequences of /+/ with a single /
         */
        return preg_replace('#/+#', '/', $path);
    }


    /**
     * Resolves '.' and '..' in the path.
     *
     * @param string $path Input path
     *
     * @return string Path with '.' and '..' resolved
     */
    private static function resolveDots(string $path): string
    {
        /**
         * Check if path is absolute
         */
        $isAbsolute = str_starts_with($path, '/');


        /**
         * Split path into components
         */
        $parts = explode('/', $path);
        $resolved = [];


        /**
         * Iterate through each component and build new path
         */
        foreach ($parts as $part) {

            /**
             * Skip empty parts and '.'
             */
            if ($part === '' || $part === '.') {
                continue;
            }


            /**
             * Handle '..' - remove previous component if possible
             */
            if ($part === '..') {
                if (!empty($resolved) && end($resolved) !== '..') {
                    array_pop($resolved);
                } elseif (!$isAbsolute) {
                    $resolved[] = '..';
                }
            } else {

                /**
                 * Add normal path component
                 */
                $resolved[] = $part;
            }
        }


        /**
         * Join components back into a path
         */
        $normalized = ($isAbsolute ? '/' : '') . implode('/', $resolved);


        /**
         * If path is empty, return '.'
         */
        return $normalized === '' ? '.' : $normalized;
    }
}
