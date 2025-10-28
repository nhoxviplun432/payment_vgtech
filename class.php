<?php

namespace vgtech;


defined('ABSPATH') || exit;

class VGTECH {
    protected array $controllers = [];
    protected array $hooks = [];


    public function __construct()
    {
        $this->loadService();
        $this->loadRoutes();
    }

    /**
     * Load helpers file để sử dụng trên toàn bộ plugin.
     */

    protected function loadService(){
        spl_autoload_register(function ($class) {
            // Chỉ xử lý các class không thuộc namespace vgtech
            if (str_starts_with($class, 'vgtech\\')) {
                return;
            }

            // Đường dẫn tới module/
            $baseDir = trailingslashit(PAYMENT_VGTECH_DIR) . 'service/';

            // Chuyển namespace -> đường dẫn
            $relative = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $class) . '.php';
            $file = $baseDir . $relative;

            if (file_exists($file)) {
                require_once $file;
                return;
            }


            $parts = explode('\\', $class);
            if (count($parts) === 2) {
                [$module, $className] = $parts;
                $file = $baseDir . "{$module}/{$className}.php";
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });
    }


    protected function loadRoutes(): void
    {
        $routePath = trailingslashit(PAYMENT_VGTECH_DIR) . 'route.php';
        if (file_exists($routePath)) {
            $routes = require $routePath;
            $this->controllers = $routes['controllers'] ?? [];
            $this->hooks = $routes['hooks'] ?? [];
        } else {
            error_log("Payment Vgtech Plugin: route.php not found at $routePath");
        }
    }


    public function register(): void
    {
        foreach ($this->hooks as $hook) {
            [$type, $hookName, $callback] = $hook;

            // Xử lý callback nếu là class method
            if (is_array($callback)) {
                $classOrInstance = $callback[0];
                $method = $callback[1];

                if (class_exists($classOrInstance)) {
                    $ref = new \ReflectionMethod($classOrInstance, $method);
                    if (!$ref->isStatic()) {
                        $instance = new $classOrInstance();
                        $callback = [$instance, $method];
                    }
                } else {
                    error_log("Vgtech Tracuu Plugin: Controller class {$classOrInstance} not found. Hook {$hookName} not registered.");
                    continue;
                }
            } elseif (is_string($callback) && !function_exists($callback)) {
                error_log("Vgtech Tracuu Plugin: Callback function {$callback} not found for hook {$hookName}.");
                continue;
            }

            // Xử lý các loại hook
            switch ($type) {
                case 'action':
                    $priority = $hook[3] ?? 10;
                    $accepted_args = $hook[4] ?? 1;
                    add_action($hookName, $callback, $priority, $accepted_args);
                    break;
                case 'filter':
                    $priority = $hook[3] ?? 10;
                    $accepted_args = $hook[4] ?? 1;
                    add_filter($hookName, $callback, $priority, $accepted_args);
                    break;
                case 'shortcode':
                    add_shortcode($hookName, $callback);
                    break;
                default:
                    error_log("IEC Plugin: Unknown hook type {$type} for hook {$hookName}.");
                    break;
            }
        }
    }


    public function run(): void {
        foreach ($this->controllers as $controllerClass) {
            if (!class_exists($controllerClass)) {
                error_log("Vgtech Plugin: Controller not found - {$controllerClass}");
                continue;
            }
            
            new $controllerClass();
        }
    }

}