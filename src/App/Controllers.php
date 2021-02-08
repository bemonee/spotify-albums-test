<?php

declare(strict_types=1);

use App\Controllers\v1\AlbumsController;
use App\Services\AlbumFinderFactory;
use Pimple\Container;

$container[AlbumsController::class] = function (Container $c) {
    return new AlbumsController(
        $c->offsetGet(AlbumFinderFactory::class)
    );
};
