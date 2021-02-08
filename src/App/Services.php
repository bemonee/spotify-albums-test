<?php

declare(strict_types=1);

use App\Services\AlbumFinderFactory;
use App\Services\SpotifyAlbumFinder;
use App\Services\SpotifyApi;
use GuzzleHttp\Client;
use Monolog\Logger;
use Pimple\Container;

$container[AlbumFinderFactory::class] = function (Container $c) {
    return new AlbumFinderFactory($c);
};

$container[SpotifyApi::class] = function (Container $c) {
    return new SpotifyApi(
        $c->offsetGet(Client::class),
        $c->offsetGet(Logger::class)
    );
};

$container[SpotifyAlbumFinder::class] = function (Container  $c) {
    return new SpotifyAlbumFinder(
        $c->offsetGet(SpotifyApi::class),
        $c->offsetGet(Logger::class)
    );
};
