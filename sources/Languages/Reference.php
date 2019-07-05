<?php
namespace Ciebit\News\Languages;

use JsonSerializable;

class Reference implements JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $languageCode;

    public function __construct(string $languageCode, string $id)
    {
        $this->id = $id;
        $this->languageCode = $languageCode;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function jsonSerialize(): array
    { 
        return [
            $this->languageCode => $this->id
        ];
    }
}
