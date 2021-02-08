<?php


namespace App\Contracts;

interface AlbumFinder
{
    public function findBy(string $bandName): array;
}
