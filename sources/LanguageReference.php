<?php
namespace Ciebit\News;

class LanguageReference
{
    private $languageCode; #string
    private $id; #id

    public function __construct(string $languageCode, int $id)
    {
        $this->languageCode = $languageCode;
        $this->id = $id;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getReferenceId(): int
    {
        return $this->id;
    }
}
