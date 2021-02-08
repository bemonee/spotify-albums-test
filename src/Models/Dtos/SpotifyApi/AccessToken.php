<?php


namespace App\Models\Dtos\SpotifyApi;

use DateTime;

class AccessToken
{
    private string $accessToken;

    private int $expires;

    private DateTime $validSince;

    public function __construct(string $jsonResponse)
    {
        $response = json_decode($jsonResponse);

        $this->accessToken = $response->access_token;
        $this->expires = (int) $response->expires;

        $this->validSince = new DateTime();
    }

    public function shouldLoginAgain(): bool
    {
        if (null == $this->accessToken) {
            return true;
        }

        $expiresAt = $this->validSince->getTimestamp() + $this->expires;
        $now = (new DateTime())->getTimestamp();

        if ($now > $expiresAt) {
            return true;
        }

        return false;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
