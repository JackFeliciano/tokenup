<?php

declare(strict_types=1);

/**
 * The whole of the loading story. No Composer, no vendor directory: a class in
 * this package lives at src/<Name>.php or src/<Folder>/<Name>.php and its name
 * is Tokenup<sep><Folder><sep><Name>.
 */
// The tests live outside src/ and carry their own namespace.
spl_autoload_register(static function (string $class): void {
    $sep = chr(92);
    $prefix = 'Tokenup' . $sep . 'Tests' . $sep;
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/tests/' . str_replace($sep, '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

spl_autoload_register(static function (string $class): void {
    $sep = chr(92);
    $prefix = 'Tokenup' . $sep;
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace($sep, '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
