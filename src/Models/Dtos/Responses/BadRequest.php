<?php


namespace App\Models\Dtos\Responses;

class BadRequest
{
    public string $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    public function toJson(): string
    {
        return json_encode($this);
    }
}
