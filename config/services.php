<?php
/**
 * Each service we want ready needs to be loaded into the app container here.
 * TODO: properly abstract this into an object that loads the various components
 */
$container = $app->getContainer();

// Setup Database connections (if any)
$db = require __DIR__ . '/../config/database.php';
if ($db) {
    foreach ($db as $name => $connection) {
        $container[$name] = function () use ($connection) {
            $pdo = new PDO($connection['connection'] . ':host=' . $connection['host'] . ';port=' . $connection['port'] . ';dbname=' . $connection['dbname'],
                $connection['user'], $connection['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $pdo;
        };
    }
}


/**
 * Setup logging using Monolog with various log levels implemented.
 * Logger Usage:
 *      $this->logger->addInfo('testing info');
 *      $this->logger->addDebug('testing debug');
 *      $this->logger->addWarning('testing warning');
 *      $this->logger->addError('testing error');
 *      $this->logger->addCritical('testing critical');
 **/
$container['logger'] = function() {
    return new \Monolog\Logger('slim-graphql', [
        new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/critical.log', \Monolog\Logger::CRITICAL, false),
        new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/error.log', \Monolog\Logger::ERROR, false),
        new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/warning.log', \Monolog\Logger::WARNING, false),
        new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/info.log', \Monolog\Logger::INFO, false),
        // Also log to stdout
//        new \Monolog\Handler\StreamHandler("php://stdout", (getenv('APP_DEBUG') ? \Monolog\Logger::INFO : \Monolog\Logger::ERROR))
    ]);
};

// Set each available controller into the container for the router
$container[\App\Controllers\BaseController::class] = function ($container) {
    return new \App\Controllers\BaseController($container);
};
