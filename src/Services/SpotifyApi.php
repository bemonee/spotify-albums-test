<?php


namespace App\Services;

use App\Exceptions\NoResultsFoundException;
use App\Models\Album;
use App\Models\Cover;
use App\Models\Dtos\SpotifyApi\AccessToken;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;

class SpotifyApi
{
    private const AUTH_ENDPOINT = 'https://accounts.spotify.com/api/token';

    private const SPOTIFY_BASE_URL = 'https://api.spotify.com/v1';

    private const ACCESS_TOKEN = 'SPOTIFY_ACCESS_TOKEN';

    private Client $httpClient;

    private Logger $logger;

    private ?AccessToken $accessToken;

    public function __construct(Client $httpClient, Logger $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;

        $this->accessToken = $_SESSION[self::ACCESS_TOKEN] ?? null;
    }

    public function logIn(): bool
    {
        if ($this->alreadyLoggedIn()) {
            return $this->accessToken;
        }

        $this->logger->info('Logging in into Spotify...');

        try {
            $token = base64_encode(
                "{$_ENV['ALBUM_FINDER_API_CLIENT_ID']}:{$_ENV['ALBUM_FINDER_API_CLIENT_SECRET']}"
            );

            $res = $this->httpClient->request('POST', self::AUTH_ENDPOINT, [
                'headers' => [
                    'Content-type' => 'application/x-www-form-urlencoded',
                    'Authorization' => "Basic {$token}"
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);
        } catch (GuzzleException $e) {
            $this->logger->error("Something was wrong logging in into Spotify: {$e->getMessage()}");

            return false;
        }

        $this->logger->info('Successfully logged in into Spotify. Registering access token');

        $this->accessToken = $_SESSION[self::ACCESS_TOKEN] = new AccessToken($res->getBody()->getContents());

        return true;
    }

    /**
     *
     * @param string $bandName
     * @return Album[]|null
     * @throws NoResultsFoundException
     */

    public function findAlbums(string $bandName): ?array
    {
        if (!$this->alreadyLoggedIn()) {
            $this->logIn();
        }

        $bandId = $this->findBandIdByBandName($bandName);

        if (null === $bandId) {
            throw new NoResultsFoundException("No results found for band name: $bandName");
        }

        $limit = 50;
        $iteration = 0;
        $albums = [];

        $items = $this->requestAlbums($bandId, $iteration, $limit);
        while (!empty($items)) {
            foreach ($items as $item) {
                $album = new Album();

                $album->name = $item->name;

                $releaseDateTime = DateTime::createFromFormat('Y-m-d', $item->release_date);
                if ($releaseDateTime) {
                    $album->released = $releaseDateTime->format('d-m-Y');
                }

                if (!empty($item->images)) {
                    $album->cover = new Cover();
                    $album->cover->url = stripslashes($item->images[0]->url);
                    $album->cover->height = $item->images[0]->height;
                    $album->cover->width = $item->images[0]->width;
                }

                $albums[$item->id] = $album;
            }

            $items = $this->requestAlbums($bandId, ++$iteration, $limit);

            if ($iteration == 3) {
                break;
            }
        }

        // To avoid multiple requests
        $this->requestAlbumTracks($albums);

        return array_values($albums);
    }

    /**
     * @param Album[] $albums
     */
    private function requestAlbumTracks(array $albums): void
    {
        $limit = 20;
        $tracks = [];

        $albumIds = array_keys($albums);
        $iterations = count($albumIds) / $limit;

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $ids = implode(',', array_slice($albumIds, ($iteration * $limit), $limit));

            $this->logger->info("Requesting Album tracks for album ids ($ids)");

            $offset = ($iteration * $limit);
            $url = self::SPOTIFY_BASE_URL."/albums?limit=$limit&offset=$offset&ids=$ids";

            try {
                $res = $this->httpClient->request('GET', $url, $this->getAuthorizationHeader());

                $json = json_decode($res->getBody()->getContents());

                foreach ($json->albums as $album) {
                    $tracks[$album->id] = count($album->tracks->items);
                }
            } catch (GuzzleException | Exception $e) {
                $this->logger->error(
                    "Something was wrong finding the track count for albums ($ids): {$e->getMessage()}"
                );
            }
        }

        foreach ($albums as $albumId => $album) {
            $album->tracks = $tracks[$albumId];
        }
    }

    private function requestAlbums(string $bandId, int $iteration, int $limit): ?array
    {
        $offset = ($iteration * $limit);

        $this->logger->info("Requesting albums for band id: $bandId (Offset $iteration)");

        $url = self::SPOTIFY_BASE_URL."/artists/{$bandId}/albums?offset=$offset&limit=$limit&include_groups=album,single";

        $this->logger->info($url);

        try {
            $res = $this->httpClient->request('GET', $url, $this->getAuthorizationHeader());
        } catch (GuzzleException $e) {
            $this->logger->error("Something was wrong finding the band id: {$e->getMessage()}");

            return null;
        }

        $json = json_decode($res->getBody()->getContents());

        if (!$json || empty($json->items)) {
            $this->logger->info("No results found for band id: $bandId");
            return null;
        }

        return $json->items;
    }

    private function findBandIdByBandName(string $bandName): ?string
    {
        $this->logger->info("Requesting band id for band name: $bandName");

        $bandName = urlencode($bandName);

        $url = self::SPOTIFY_BASE_URL."/search?q=$bandName&type=artist&limit=1";

        try {
            $res = $this->httpClient->request('GET', $url, $this->getAuthorizationHeader());
        } catch (GuzzleException $e) {
            $this->logger->error("Something was wrong finding the band id: {$e->getMessage()}");

            return null;
        }

        $json = json_decode($res->getBody()->getContents());

        if (!$json || empty($json->artists->items)) {
            $this->logger->info("No results found for band name: $bandName");
            return null;
        }

        $bandId = $json->artists->items[0]->id;

        $this->logger->info("Id for band name $bandName is $bandId");

        return $bandId;
    }

    private function alreadyLoggedIn(): bool
    {
        return null !== $this->accessToken && !$this->accessToken->shouldLoginAgain();
    }

    private function getAuthorizationHeader(): array
    {
        return [
            'headers' => [
                'Authorization' => "Bearer {$this->accessToken->getAccessToken()}",
            ],
        ];
    }
}
