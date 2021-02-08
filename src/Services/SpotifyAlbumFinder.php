<?php


namespace App\Services;

use App\Contracts\AlbumFinder;
use Monolog\Logger;

class SpotifyAlbumFinder implements AlbumFinder
{
    private SpotifyApi $spotifyApi;

    private Logger $logger;

    public function __construct(SpotifyApi $spotifyApi, Logger $logger)
    {
        $this->spotifyApi = $spotifyApi;
        $this->logger = $logger;
    }

    public function findBy(string $bandName): array
    {
        $this->logger->info('Logging in into Spotify...');

        $this->spotifyApi->logIn();
        
        $this->logger->info("Searching albums for band name: $bandName");

        return $this->spotifyApi->findAlbums($bandName);
    }
}
