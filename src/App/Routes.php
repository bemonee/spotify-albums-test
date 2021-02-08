<?php

declare(strict_types=1);

$app->get('/api/v1/albums', 'App\Controllers\v1\AlbumsController:findAlbums');
