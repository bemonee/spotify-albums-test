<?php

declare(strict_types=1);

namespace App\Controllers\v1;

use App\Contracts\AlbumFinder;
use App\Exceptions\InvalidAlbumFinderConfigException;
use App\Helper\JsonResponse;
use App\Models\Dtos\Responses\BadRequest;
use App\Services\AlbumFinderFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class AlbumsController
{
    private AlbumFinder $albumFinder;

    public function __construct(AlbumFinderFactory $albumFinderFactory)
    {
        $this->albumFinder = $albumFinderFactory->getAlbumFinder($_ENV['ALBUM_FINDER_SERVICE']);
    }

    public function findAlbums(Request $request, Response $response): Response
    {
        $bandName = $request->getQueryParams()['q'];

        if (null === $bandName) {
            $badRequestDto = new BadRequest('Mandatory "q" param was not given');
            return JsonResponse::withJson($response, $badRequestDto->toJson(), 400);
        }

        $albums = $this->albumFinder->findBy($bandName);

        return JsonResponse::withJson($response, json_encode($albums), 200);
    }
}
