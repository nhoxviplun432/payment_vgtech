<?php

namespace paymentvgtech;


defined('ABSPATH') || exit;

class PaymentVgtech {
    protected array $controllers = [];
    protected array $hooks = [];


    public function __construct()
    {
        $this->loadRoutes();
    }

    protected function loadRoutes(): void
    {
        $routePath = trailingslashit(PAYMENT_AI_CHAT_VGTECH_DIR) . 'src/includes/route.php';
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

            // Nếu callback là mảng [Class, Method]
            if (is_array($callback)) {
                [$classOrInstance, $method] = $callback;

                if (!class_exists($classOrInstance)) {
                    error_log("❌ Controller class {$classOrInstance} not found for hook {$hookName}.");
                    continue;
                }

                if (!method_exists($classOrInstance, $method)) {
                    error_log("❌ Method {$method} not found in class {$classOrInstance} for hook {$hookName}.");
                    continue;
                }

                // Nếu là non-static → khởi tạo instance
                $ref = new \ReflectionMethod($classOrInstance, $method);
                if (!$ref->isStatic()) {
                    $instance = new $classOrInstance();
                    $callback = [$instance, $method];
                }
            } elseif (is_string($callback) && !function_exists($callback)) {
                error_log("❌ Callback function {$callback} not found for hook {$hookName}.");
                continue;
            }

            // Đăng ký hook tương ứng
            $priority = $hook[3] ?? 10;
            $accepted_args = $hook[4] ?? 1;

            switch ($type) {
                case 'action':
                    add_action($hookName, $callback, $priority, $accepted_args);
                    break;
                case 'filter':
                    add_filter($hookName, $callback, $priority, $accepted_args);
                    break;
                case 'shortcode':
                    add_shortcode($hookName, $callback);
                    break;
                default:
                    error_log("⚠️ Unknown hook type {$type} for {$hookName}");
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