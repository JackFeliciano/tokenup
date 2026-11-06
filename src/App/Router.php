<?php

declare(strict_types=1);

namespace Tokenup\App;

/**
 * Path in, handler out. The base prefix is stripped once, here, so a request
 * that arrives from nginx with its own prefix still matches the route table.
 */
final class Router
{
    /** @var array<string, callable> */
    private array $routes = [];

    public function __construct(private readonly string $base = '/')
    {
    }

    public function get(string $path, callable $handler): void
    {
        $this->routes[$this->normalise($path)] = $handler;
    }

    private function normalise(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : $path . '/';
    }

    private function strip(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim($this->base, '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        return '/' . trim($uri, '/');
    }

    public function handle(string $method, string $uri): array
    {
        $path = $this->strip($uri);
        $key = $this->normalise($path);
        if (!isset($this->routes[$key])) {
            return [404, ['error' => 'not found', 'path' => $path]];
        }

        return (array) ($this->routes[$key])($path);
    }
}
