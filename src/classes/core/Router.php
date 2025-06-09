<?php
namespace Core;

class Router {
    // Uchovává všechny definované routes
    private array $routes = [];
    
    // Výchozí namespace pro kontrolery
    private array $namespaces = [
        'api' => 'Api\\V1\\Controllers\\',
        'default' => 'Controllers\\'
    ];

    /**
     * Konstruktor umožňující rozšíření výchozích namespace
     * @param array $additionalNamespaces Další namespace pro routing
     */
    public function __construct(array $additionalNamespaces = []) {
        $this->namespaces = array_merge($this->namespaces, $additionalNamespaces);
    }

    /**
     * Přidání route pro libovolnou HTTP metodu
     * @param string $method HTTP metoda
     * @param string $route URL cesta
     * @param string $handler Kontroler a akce ve formátu "Controller@method"
     * @param array $options Další volitelné parametry
     * @return self
     */
    public function addRoute(string $method, string $route, string $handler, array $options = []): self {
        $this->routes[$method][$route] = [
            'handler' => $handler,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Zástupné metody pro běžné HTTP metody
     */
    public function get(string $route, string $handler, array $options = []): self {
        return $this->addRoute('GET', $route, $handler, $options);
    }

    public function post(string $route, string $handler, array $options = []): self {
        return $this->addRoute('POST', $route, $handler, $options);
    }

    public function put(string $route, string $handler, array $options = []): self {
        return $this->addRoute('PUT', $route, $handler, $options);
    }

    public function delete(string $route, string $handler, array $options = []): self {
        return $this->addRoute('DELETE', $route, $handler, $options);
    }

    /**
     * Hlavní dispečerská metoda pro zpracování požadavků
     * @param string $url Požadovaná URL
     * @return mixed Výsledek zpracování route
     */
    public function dispatch(string $url) {
        // Normalizace URL
        $url = trim($url, '/');
        
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
    private function matchRoute(string $url, string $method) {
        // Přesná shoda route
        if (isset($this->routes[$method][$url])) {
            return $this->executeHandler($url, $method);
        }

        // Hledání parametrizované route
        foreach ($this->routes[$method] as $route => $routeData) {
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
    private function isParametricRoute(string $route): bool {
        return strpos($route, '{') !== false && strpos($route, '}') !== false;
    }

    /**
     * Zpracování parametrizované route
     * @param string $url Požadovaná URL
     * @param string $route Definovaná route
     * @return array|null Nalezené parametry nebo null
     */
    private function matchParametricRoute(string $url, string $route): ?array {
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
    private function executeHandler(string $url, string $method) {
        $routeData = $this->routes[$method][$url];
        
        // Rozdělení handleru na kontroler a akci
        [$controllerName, $action] = explode('@', $routeData['handler']);
        
        // Určení plného namespace kontroleru
        $controller = $this->resolveControllerNamespace($url, $controllerName);
        
        // Vyvolání metody kontroleru
        return $this->invokeControllerMethod($controller, $action);
    }

    /**
     * Spuštění handleru pro parametrizovanou route
     * @param string $route Definovaná route
     * @param string $method HTTP metoda
     * @param array $params Nalezené parametry
     * @return mixed Výsledek zpracování
     */
    private function executeParametricHandler(string $route, string $method, array $params) {
        $routeData = $this->routes[$method][$route];
        
        // Rozdělení handleru na kontroler a akci
        [$controllerName, $action] = explode('@', $routeData['handler']);
        
        // Určení plného namespace kontroleru
        $controller = $this->resolveControllerNamespace($route, $controllerName);
        
        // Vyvolání metody kontroleru s parametry
        return $this->invokeControllerMethod($controller, $action, array_values($params));
    }

    /**
     * Vyvolání metody kontroleru
     * @param string $controller Plně kvalifikovaný název kontroleru
     * @param string $action Název metody
     * @param array $params Parametry metody
     * @return mixed Výsledek volání metody
     * @throws \Exception Pokud kontroler nebo metoda neexistuje
     */
    private function invokeControllerMethod(string $controller, string $action, array $params = []) {
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

        // Volání metody s parametry
        return call_user_func_array([$controllerObject, $action], $params);
    }

    /**
     * Určení namespace kontroleru
     * @param string $route Definovaná route
     * @param string $controllerName Název kontroleru
     * @return string Plně kvalifikovaný název kontroleru
     */
    private function resolveControllerNamespace(string $route, string $controllerName): string {
        // Pokud je již uveden plný namespace, vrátíme ho
        if (strpos($controllerName, '\\') !== false) {
            return $controllerName;
        }

        // Hledání specifického namespace podle prefixu route
        foreach ($this->namespaces as $prefix => $namespace) {
            if (strpos($route, $prefix . '/') === 0) {
                return $namespace . $controllerName;
            }
        }

        // Výchozí namespace
        return $this->namespaces['default'] . $controllerName;
    }

    /**
     * Protokolování dispatche
     * @param string $url Požadovaná URL
     * @param string $method HTTP metoda
     */
    private function logDispatch(string $url, string $method): void {
        error_log("Dispatching URL: $url, Method: $method");
    }

    /**
     * Zjištění, zda jde o API route
     * @param string $url Požadovaná URL
     * @return bool Zda jde o API route
     */
    private function isApiRoute(string $url): bool {
        return strpos($url, 'api/') === 0;
    }

    /**
     * Zpracování chyby route
     * @param \Exception $e Zachycená výjimka
     * @return mixed Výsledek zpracování chyby
     */
    private function handleRouteError(\Exception $e) {
        // Protokolování chyby
        error_log($e->getMessage());
        
        // Zobrazení 404
        $this->show404();
    }

    /**
     * Zobrazení 404 chyby
     */
    private function show404(): void {
        // Zjistíme URL
        $url = $_GET['url'] ?? '';
        
        // Nastavení HTTP hlavičky
        header("HTTP/1.0 404 Not Found");

        // Rozlišení mezi API a webovou routou
        if ($this->isApiRoute($url)) {
            // Pro API route vrátíme JSON
            header('Content-Type: application/json; charset=utf-8');
            
            $response = [
                'success' => false,
                'message' => 'Stránka nebo endpoint nebyl nalezen',
                'statusCode' => 404
            ];
            
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        } else {
            // Pro webovou route použijeme HTML layout
            ob_start();
            include "../src/views/errors/404.php";
            $content = ob_get_clean();
            
            // Data pro layout
            $data = [
                'title' => 'Stránka nenalezena | Kali-framework',
                'content' => $content
            ];
            
            // Načtení layoutu
            include "../src/views/layouts/main.php";
            exit();
        }
    }
}