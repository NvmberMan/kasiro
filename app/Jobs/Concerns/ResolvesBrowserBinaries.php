<?php

namespace App\Jobs\Concerns;

/**
 * Shared resolution of the Chrome / Node / npm executables that Browsershot needs.
 * Used by the tenant and template screenshot jobs so the platform-specific path
 * probing lives in one place.
 */
trait ResolvesBrowserBinaries
{
    protected function resolveChromeExecutable(): ?string
    {
        $paths = [
            config('browsershot.chrome_path'),
            'C:/Program Files/Google/Chrome/Application/chrome.exe',
            'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
        ];

        foreach ($paths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function resolveNodePath(): ?string
    {
        if ($configured = config('browsershot.node_path')) {
            return file_exists($configured) ? $configured : null;
        }

        $candidates = [
            'C:/Program Files/nodejs/node.exe',
            '/usr/bin/node',
            '/usr/local/bin/node',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function resolveNpmPath(): ?string
    {
        if ($configured = config('browsershot.npm_path')) {
            return file_exists($configured) ? $configured : null;
        }

        $candidates = [
            'C:/Program Files/nodejs/npm.cmd',
            '/usr/bin/npm',
            '/usr/local/bin/npm',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
