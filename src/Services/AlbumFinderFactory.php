<?php


namespace App\Services;

use App\Contracts\AlbumFinder;
use App\Enums\AvailableAlbumFindersEnum;
use App\Exceptions\InvalidAlbumFinderConfigException;
use Pimple\Container;

class AlbumFinderFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getAlbumFinder(string $configuredAlbumFinder): AlbumFinder
    {
        if ($this->configuredAlbumFinderIsInvalid($configuredAlbumFinder)) {
            throw new InvalidAlbumFinderConfigException($configuredAlbumFinder);
        }

        switch ($configuredAlbumFinder) {
            case AvailableAlbumFindersEnum::SPOTIFY:
                return $this->container->offsetGet(SpotifyAlbumFinder::class);
        }
    }

    private function configuredAlbumFinderIsInvalid(string $configuredAlbumFinder): bool
    {
        return !in_array($configuredAlbumFinder, AvailableAlbumFindersEnum::getAvailableServices());
    }
}
