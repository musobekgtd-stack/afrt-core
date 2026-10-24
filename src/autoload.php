<?php

declare(strict_types=1);

/**
 * AFRT Core Standalone Autoloader (PSR-4 compliant)
 * Allows executing afrt-core without external vendor dependencies.
 */
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'Afrt\\Core\\Tests\\' => __DIR__ . '/../tests/',
        'Afrt\\Core\\' => __DIR__ . '/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
