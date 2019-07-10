<?php

/**
 * Class HttpServer - Swoole http server using Slim framework application on request
 */
class HttpServer
{
    public static $instance;
    public static $get;
    public static $post;
    public static $header;
    public static $server;

    /** @var Swoole\Http\Response $response */
    protected $response;

    /**
     * HttpServer constructor.
     */
    public function __construct()
    {
        $http = new Swoole\Http\Server('0.0.0.0', getenv('APP_PORT'));

        $http->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
            $this->response = $response;

//            register_shutdown_function([$this, 'handleFatal']); // I think this is the cause of a memory leak
            $response->header('Content-Type', 'application/json');

            // Ignore favicon requests
            if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
                return $response->end();
            }

            ob_start();

            try {
                // Load package dependencies
                require __DIR__ . '/../vendor/autoload.php';

                // Load Environment variables form .env file
                (Dotenv\Dotenv::create(substr(__DIR__, 0, strrpos(__DIR__, '/'))))->load();

                // Create Slim request out of the Swoole request
                $c = new \Slim\Container(['request' => function () use ($request) {
                    $server = [];
                    // Hydrate this instance with the request information
                    if (isset($request->server)) {
                        foreach ($request->server as $key => $value) {
                            $server[strtoupper($key)] = $value;
                        }
                    }

                    // TODO: hydrate $query in Uri creation
                    $uri = new \Slim\Http\Uri(
                        'http',
                        $request->header['host'],
                        $request->server['server_port'],
                        $request->server['path_info']
                    );

                    $headers = new \Slim\Http\Headers($request->header);

                    $stream = fopen('php://memory','r+');
                    fwrite($stream, $request->rawContent());
                    rewind($stream);

                    return new \Slim\Http\Request(
                        $request->server['request_method'],
                        $uri,
                        $headers,
                        [],
                        $server,
                        new \Slim\Http\Stream($stream)
                    );
                }]);
                $app = new \Slim\App($c);

                // Add all services to the App container
                include __DIR__ . '/../config/services.php';

                // Register routes
                include __DIR__ . '/../config/routes.php';

                $app->run();

                $result = ob_get_contents();
                ob_end_clean();

                $code = 0;
                $response->end($result);

                unset($result);
            } catch (Exception $e) {
                // If we can log the error than do so
                if (isset($container) && $container->logger) {
                    $container->logger->addError($e->getMessage());
                }

                $code = $e->getCode();
                $response->end(json_encode(['error' => $e->getMessage()]));

                unset($e);
            }

            // Cleanup for next request
            unset($app, $container, $db, $this->response, $response, $request);

            return $code;
        });

        $http->start();
    }

//    /**
//     * Fatal Error
//     */
//    public function handleFatal()
//    {
//        $error = error_get_last();
//
//        if (!isset($error['type'])) return;
//
//        switch ($error['type']) {
//            case E_ERROR:
//            case E_PARSE:
//            case E_DEPRECATED:
//            case E_CORE_ERROR:
//            case E_COMPILE_ERROR:
//                break;
//            default:
//                return;
//        }
//
//        $message = $error['message'];
//        $file    = $error['file'];
//        $line    = $error['line'];
//        $log     = "\n$message ($file:$line)\nStack trace:\n";
//        $trace   = debug_backtrace(1);
//
//        foreach ($trace as $i => $t) {
//            if (!isset($t['file'])) $t['file'] = 'unknown';
//            if (!isset($t['line'])) $t['line'] = 0;
//            if (!isset($t['function'])) $t['function'] = 'unknown';
//
//            $log .= "#$i {$t['file']}({$t['line']}): ";
//
//            if (isset($t['object']) && is_object($t['object'])) $log .= get_class($t['object']) . '->';
//
//            $log .= "{$t['function']}()\n";
//        }
//
//        if (isset($_SERVER['REQUEST_URI'])) $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
//
//        if ($this->response) {
//            $this->response->status(500);
//            $this->response->end($log);
//        }
//        unset($this->response);
//    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

HttpServer::getInstance();