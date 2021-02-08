<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Monolog\Logger;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;
use Slim\Factory\AppFactory;

$container = new Container();

$container[Logger::class] = function ($c) {
    $logger = new Logger('app-log');
    $logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Logger::DEBUG));
    return $logger;
};

$container[Client::class] = function ($c) {
    return new Client();
};

$app = AppFactory::create(null, new Psr11Container($container));

return $app;
