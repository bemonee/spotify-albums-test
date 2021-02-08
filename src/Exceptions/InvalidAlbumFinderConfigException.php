<?php


namespace App\Exceptions;

use App\Enums\AvailableAlbumFindersEnum;
use Exception;

class InvalidAlbumFinderConfigException extends Exception
{
    public function __construct(string $invalidAlbumFinder)
    {
        parent::__construct();

        $albumFinders = AvailableAlbumFindersEnum::getAvailableServices();
        $this->message = "$invalidAlbumFinder is not supported ATM. Available album finders are: {$albumFinders}";
    }
}
