<?php


namespace App\Enums;

class AvailableAlbumFindersEnum
{
    public const SPOTIFY = 'SPOTIFY';

    private const AVAILABLE_SERVICES = [
        self::SPOTIFY
    ];

    public static function getAvailableServices(): array
    {
        return self::AVAILABLE_SERVICES;
    }
}
