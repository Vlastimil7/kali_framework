<?php

namespace Core;

use Helpers\Logger;

class Router
{
    // Uchovává všechny definované routes
    private array $routes = [];
    private array $groupOptions = [];
    private ?array $lastRoute = null;
    private ?Request $request = null;

    private array $middlewareAliases = [
        'auth' => \Middleware\AuthMiddleware::class,
        'admin' => \Middleware\AdminMiddleware::class,
        'csrf' => \Middleware\CsrfMiddleware::class,
    ];

    // Výchozí namespace pro kontrolery
    private array $namespaces = [
        'api'     => 'Api\\V1\\Controllers\\',
        'admin'   => 'Controllers\\Admin\\',
        'default' => 'Controllers\\Front\\',
    ];

    /**
     * Konstruktor umožňující rozšíření výchozích namespace
     * @param array $additionalNamespaces Další namespace pro routing
     */
    public function __construct(array $additionalNamespaces = [], array $middlewareAliases = [])
    {
        $this->namespaces = array_merge($this->namespaces, $additionalNamespaces);
        $this->middlewareAliases = array_merge($this->middlewareAliases, $middlewareAliases);
    }

    /**
     * Přidání route pro libovolnou HTTP metodu
     * @param string $method HTTP metoda
     * @param string $route URL cesta
     * @param string $handler Kontroler a akce ve formátu "Controller@method"
     * @param array $options Další volitelné parametry
     * @return self
     */
    public function addRoute(string $method, string $route, string $handler, array $options = []): self
    {
        $method = strtoupper($method);
        $options = $this->mergeOptions($this->currentGroupOptions(), $options);
        $this->routes[$method][$route] = [
            'handler' => $handler,
            'options' => $options
        ];
        $this->lastRoute = [$method, $route];
        return $this;
    }

    /**
     * Přidá middleware k naposledy zaregistrované routě.
     */
    public function middleware(string|array $middleware): self
    {
        if ($this->lastRoute === null) {
            throw new \LogicException('Middleware nelze přiřadit před registrací routy.');
        }

        [$method, $route] = $this->lastRoute;
        $current = $this->routes[$method][$route]['options']['middleware'] ?? [];
        $this->routes[$method][$route]['options']['middleware'] = array_values(array_unique([
            ...$this->normalizeMiddleware($current),
            ...$this->normalizeMiddleware($middleware),
        ]));

        return $this;
    }

    /**
     * Seskupí routy pod společné options, typicky middleware.
     */
    public function group(array $options, callable $routes): self
    {
        $this->groupOptions[] = $options;

        try {
            $routes($this);
        } finally {
            array_pop($this->groupOptions);
        }

        return $this;
    }

    public function aliasMiddleware(string $alias, string $middlewareClass): self
    {
        $this->middlewareAliases[$alias] = $middlewareClass;

        return $this;
    }

    /**
     * Zástupné metody pro běžné HTTP metody
     */
    public function get(string $route, string $handler, array $options = []): self
    {
        return $this->addRoute('GET', $route, $handler, $options);
    }

    public function post(string $route, string $handler, array $options = []): self
    {
        return $this->addRoute('POST', $route, $handler, $options);
    }

    public function put(string $route, string $handler, array $options = []): self
    {
        return $this->addRoute('PUT', $route, $handler, $options);
    }

    public function delete(string $route, string $handler, array $options = []): self
    {
        return $this->addRoute('DELETE', $route, $handler, $options);
    }

    /**
     * Hlavní dispečerská metoda pro zpracování požadavků
     * @param string $url Požadovaná URL
     * @return mixed Výsledek zpracování route
     */
    public function dispatch(string $url)
    {
        // The bootstrap normally initializes localization. Keeping this guard
        // makes direct Router usage deterministic as well.
        $url = isset($GLOBALS['localized_route_path'])
            ? current_route_path()
            : initialize_localized_request($url);
        $url = trim($url, '/');
        $this->request = Request::capture();

        // Zjištění HTTP metody
        $method = $_SERVER['REQUEST_METHOD'];

        // Protokolování
        $this->logDispatch($url, $method);

        try {
            // Pokus o nalezení a zpracování route
            return $this->matchRoute($url, $method);
        } catch (\Exception $e) {
            // Zpracování chyb
            return $this->handleRouteError($e);
        }
    }

    /**
     * Nalezení a zpracování route
     * @param string $url Normalizovaná URL
     * @param string $method HTTP metoda
     * @return mixed Výsledek zpracování route
     */
    private function matchRoute(string $url, string $method)
    {
        if ($method === 'HEAD' && !isset($this->routes['HEAD'][$url])) {
            $method = 'GET';
        }
        $methodRoutes = $this->routes[$method] ?? [];

        // Přesná shoda route
        if (isset($methodRoutes[$url])) {
            return $this->executeHandler($url, $method);
        }

        // Hledání parametrizované route
        foreach ($methodRoutes as $route => $routeData) {
            if ($this->isParametricRoute($route)) {
                $matchResult = $this->matchParametricRoute($url, $route);
                if ($matchResult) {
                    return $this->executeParametricHandler($route, $method, $matchResult);
                }
            }
        }

        // Žádná shoda - zobrazení 404
        $this->show404();
    }

    /**
     * Kontrola, zda jde o parametrizovanou route
     * @param string $route Cesta route
     * @return bool Zda obsahuje parametry
     */
    private function isParametricRoute(string $route): bool
    {
        return strpos($route, '{') !== false && strpos($route, '}') !== false;
    }

    /**
     * Zpracování parametrizované route
     * @param string $url Požadovaná URL
     * @param string $route Definovaná route
     * @return array|null Nalezené parametry nebo null
     */
    private function matchParametricRoute(string $url, string $route): ?array
    {
        // Převedení route na regulární výraz
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
        $pattern = "#^$pattern$#";

        // Kontrola shody
        if (preg_match($pattern, $url, $matches)) {
            // Extrakce názvů parametrů
            preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route, $paramNames);

            // Odstranění první položky (celková shoda)
            array_shift($matches);

            // Spojení názvů parametrů s hodnotami
            return array_combine($paramNames[1], $matches);
        }

        return null;
    }

    /**
     * Spuštění handleru pro přesnou shodu route
     * @param string $url Požadovaná URL
     * @param string $method HTTP metoda
     * @return mixed Výsledek zpracování
     */
    private function executeHandler(string $url, string $method)
    {
        $routeData = $this->routes[$method][$url];

        // Rozdělení handleru na kontroler a akci
        [$controllerName, $action] = explode('@', $routeData['handler']);

        // Určení plného namespace kontroleru
        $controller = $this->resolveControllerNamespace($url, $controllerName);
        $this->request?->setRouteParams([]);

        // Vyvolání metody kontroleru
        return $this->runMiddleware(
            $routeData['options']['middleware'] ?? [],
            fn () => $this->invokeControllerMethod($controller, $action)
        );
    }

    /**
     * Spuštění handleru pro parametrizovanou route
     * @param string $route Definovaná route
     * @param string $method HTTP metoda
     * @param array $params Nalezené parametry
     * @return mixed Výsledek zpracování
     */
    private function executeParametricHandler(string $route, string $method, array $params)
    {
        $routeData = $this->routes[$method][$route];

        // Rozdělení handleru na kontroler a akci
        [$controllerName, $action] = explode('@', $routeData['handler']);

        // Určení plného namespace kontroleru
        $controller = $this->resolveControllerNamespace($route, $controllerName);
        $this->request?->setRouteParams($params);

        // Vyvolání metody kontroleru s parametry
        return $this->runMiddleware(
            $routeData['options']['middleware'] ?? [],
            fn () => $this->invokeControllerMethod($controller, $action, array_values($params))
        );
    }

    private function runMiddleware(string|array $middleware, callable $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->normalizeMiddleware($middleware)),
            function (callable $next, string $name): callable {
                return function () use ($name, $next) {
                    $class = $this->middlewareAliases[$name] ?? $name;

                    if (!class_exists($class)) {
                        throw new \RuntimeException("Middleware {$name} nebyl nalezen.");
                    }

                    $middleware = new $class();
                    if (!$middleware instanceof MiddlewareInterface) {
                        throw new \RuntimeException("Middleware {$class} musí implementovat " . MiddlewareInterface::class . '.');
                    }

                    return $middleware->handle($this->request ?? Request::capture(), $next);
                };
            },
            $destination
        );

        return $pipeline();
    }

    private function currentGroupOptions(): array
    {
        $options = [];
        foreach ($this->groupOptions as $groupOptions) {
            $options = $this->mergeOptions($options, $groupOptions);
        }

        return $options;
    }

    private function mergeOptions(array $base, array $override): array
    {
        $middleware = [
            ...$this->normalizeMiddleware($base['middleware'] ?? []),
            ...$this->normalizeMiddleware($override['middleware'] ?? []),
        ];

        $options = array_merge($base, $override);
        if ($middleware !== []) {
            $options['middleware'] = array_values(array_unique($middleware));
        }

        return $options;
    }

    private function normalizeMiddleware(string|array $middleware): array
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }

        return array_values(array_filter(
            array_map(static fn ($name): string => trim((string)$name), $middleware),
            static fn (string $name): bool => $name !== ''
        ));
    }

    /**
     * Vyvolání metody kontroleru
     * @param string $controller Plně kvalifikovaný název kontroleru
     * @param string $action Název metody
     * @param array $params Parametry metody
     * @return mixed Výsledek volání metody
     * @throws \Exception Pokud kontroler nebo metoda neexistuje
     */
    private function invokeControllerMethod(string $controller, string $action, array $params = [])
    {
        // Kontrola existence kontroleru
        if (!class_exists($controller)) {
            throw new \Exception("Kontroler $controller nebyl nalezen");
        }

        // Vytvoření instance kontroleru
        $controllerObject = new $controller();

        // Kontrola existence metody
        if (!method_exists($controllerObject, $action)) {
            throw new \Exception("Metoda $action nebyla nalezena v kontroleru $controller");
        }

        $reflection = new \ReflectionMethod($controllerObject, $action);
        $methodParams = $reflection->getParameters();
        $firstType = isset($methodParams[0]) ? $methodParams[0]->getType() : null;

        if ($firstType instanceof \ReflectionNamedType && $firstType->getName() === Request::class) {
            array_unshift($params, $this->request ?? Request::capture());
        }

        return $reflection->invokeArgs($controllerObject, $params);
    }

    /**
     * Určení namespace kontroleru
     * @param string $route Definovaná route
     * @param string $controllerName Název kontroleru
     * @return string Plně kvalifikovaný název kontroleru
     */
    private function resolveControllerNamespace(string $route, string $controllerName): string
    {
        // 1) Pokud je to už plně kvalifikované (Controllers\..., Api\...), nech to být
        if (str_starts_with($controllerName, 'Controllers\\') || str_starts_with($controllerName, 'Api\\')) {
            return $controllerName;
        }

        // 2) Alias zápis: "Admin\XController" nebo "Front\XController"
        if (str_contains($controllerName, '\\')) {
            return 'Controllers\\' . $controllerName; // => Controllers\Admin\XController
        }

        // 3) Podle prefixu route
        foreach ($this->namespaces as $prefix => $namespace) {
            if ($prefix !== 'default' && str_starts_with($route, $prefix . '/')) {
                return $namespace . $controllerName;
            }
        }

        // 4) Default = Front
        return $this->namespaces['default'] . $controllerName;
    }

    /**
     * Protokolování dispatche
     * @param string $url Požadovaná URL
     * @param string $method HTTP metoda
     */
    private function logDispatch(string $url, string $method): void
    {

        //  Logger::info("Dispatching URL: $url, Method: $method");

    }

    /**
     * Zjištění, zda jde o API route
     * @param string $url Požadovaná URL
     * @return bool Zda jde o API route
     */
    private function isApiRoute(string $url): bool
    {
        return strpos($url, 'api/') === 0;
    }

    /**
     * Zpracování chyby route
     * @param \Exception $e Zachycená výjimka
     * @return mixed Výsledek zpracování chyby
     */
    private function handleRouteError(\Exception $e)
    {
        // Protokolování chyby
        Logger::error("Route error: " . $e->getMessage());

        // Zobrazení 404
        $this->show404();
    }

    /**
     * Zobrazení 404 chyby
     */
    private function show404(): void
    {
        // Zjistíme URL
        $url = current_route_path();

        // Nastavení HTTP hlavičky
        http_response_code(404);

        // Rozlišení mezi API a webovou routou
        if ($this->isApiRoute($url)) {
            // Pro API route vrátíme JSON
            header('Content-Type: application/json; charset=utf-8');

            $response = [
                'success' => false,
                'message' => __('page_not_found', [], '404'),
                'statusCode' => 404
            ];

            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        } else {
            // Pro webovou route použijeme HTML layout
            ob_start();
            include ROOT_PATH . "/src/views/errors/404.php";
            $content = ob_get_clean();

            // Data pro layout
            $data = [
                'title' => __('page_not_found', [], '404') . ' | VK-DEV.cz',
                'content' => $content,
                'noindex' => true,
            ];

            // Načtení layoutu
            include ROOT_PATH . "/src/views/layouts/main.php";
            exit();
        }
    }
}
