<?php

namespace Core;

class Request
{
    private array $routeParams = [];

    public function __construct(
        private array $query = [],
        private array $request = [],
        private array $files = [],
        private array $server = [],
        private array $cookies = [],
    ) {
    }

    public static function capture(): self
    {
        $request = $_POST;
        $contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));

        if (str_contains($contentType, 'application/json')) {
            $json = json_decode((string)file_get_contents('php://input'), true);
            if (is_array($json)) {
                $request = $json;
            }
        }

        return new self($_GET, $request, $_FILES, $_SERVER, $_COOKIE);
    }

    public function method(): string
    {
        return strtoupper((string)($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function all(): array
    {
        return array_replace_recursive($this->query, $this->request);
    }

    public function input(string $key, $default = null)
    {
        return $this->value($this->all(), $key, $default);
    }

    public function post(string $key = '', $default = null)
    {
        return $key === '' ? $this->request : $this->value($this->request, $key, $default);
    }

    public function query(string $key = '', $default = null)
    {
        return $key === '' ? $this->query : $this->value($this->query, $key, $default);
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);

        return is_scalar($value) ? trim((string)$value) : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->input($key, $default);

        return is_numeric($value) ? (int)$value : $default;
    }

    public function boolean(string $key, bool $default = false): bool
    {
        $value = $this->input($key, null);
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public function has(string $key): bool
    {
        return $this->input($key, null) !== null;
    }

    public function filled(string $key): bool
    {
        $value = $this->input($key, null);

        return $value !== null && $value !== '' && $value !== [];
    }

    public function only(array $keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            if ($this->has((string)$key)) {
                $values[$key] = $this->input((string)$key);
            }
        }

        return $values;
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    public function file(string $key, $default = null)
    {
        return $this->value($this->files, $key, $default);
    }

    public function header(string $key, $default = null)
    {
        $normalized = strtoupper(str_replace('-', '_', $key));
        $serverKey = in_array($normalized, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)
            ? $normalized
            : 'HTTP_' . $normalized;

        return $this->server[$serverKey] ?? $default;
    }

    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    public function ip(): string
    {
        return (string)($this->server['REMOTE_ADDR'] ?? '');
    }

    public function uri(): string
    {
        return (string)($this->server['REQUEST_URI'] ?? '');
    }

    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            || strtolower((string)($this->server['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function route(string $key = '', $default = null)
    {
        return $key === '' ? $this->routeParams : ($this->routeParams[$key] ?? $default);
    }

    private function value(array $source, string $key, $default = null)
    {
        if ($key === '') {
            return $source;
        }

        $value = $source;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
