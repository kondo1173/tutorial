<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Mvc\Dispatcher;

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . '/controllers/',
        APP_PATH . '/models/',
        APP_PATH . '/Session/Adapter/',
    ]
);
$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    "dispatcher",
    function () {
        $dispatcher = new Dispatcher();
        return $dispatcher;
    }
);

$container->set(
    'db',
    function () {
        return new Mysql(
            [
                'host'     => 'ramuh.his',
                'username' => 'db_handson_kondo',
                'password' => 'CPQ7K7y8Pv9WaIV)',
                'dbname'   => 'db_handson_kondo',
            ]
        );
    }
);

$container->setShared(
    'session',
    function () use($container) {
        $session = new Manager();
        $adapter = new Database(
            [
                'db' => $container->get('db'),
                'table' => 'session_data'
            ]
        );
        $session->setAdapter($adapter);
        $session->start();
        return $session;
    }
);



$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$application = new Application($container);

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}

