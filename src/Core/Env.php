<?php

namespace App\Core;

final class Env
{
    public static function load(string $path, array $overrideKeys = []): void
    {
        if (!is_file($path)) {
            return;
        }

        $overrideLookup = [];
        foreach ($overrideKeys as $overrideKey) {
            $normalized = trim((string) $overrideKey);
            if ($normalized !== '') {
                $overrideLookup[$normalized] = true;
            }
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"");

            if ($key === '') {
                continue;
            }

            $alreadyDefined = array_key_exists($key, $_ENV);
            $allowOverride = isset($overrideLookup[$key]);
            if ($alreadyDefined && !$allowOverride) {
                continue;
            }

            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}
