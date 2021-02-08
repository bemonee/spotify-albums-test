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

    public function getAlbumFinder(string $configuredAlbumFinderService): AlbumFinder
    {
        if ($this->isConfiguredAlbumFinderServiceValid($configuredAlbumFinderService)) {
            throw new InvalidAlbumFinderConfigException($configuredAlbumFinderService);
        }

        switch ($configuredAlbumFinderService) {
            case AvailableAlbumFindersEnum::SPOTIFY:
                return $this->container->offsetGet(SpotifyAlbumFinder::class);
        }
    }

    private function isConfiguredAlbumFinderServiceValid(string $configuredAlbumFinder): bool
    {
        return !in_array($configuredAlbumFinder, AvailableAlbumFindersEnum::getAvailableServices());
    }
}
