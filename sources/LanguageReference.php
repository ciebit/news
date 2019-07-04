<?php
namespace Ciebit\News;

class LanguageReference
{
    /** @var string */
    private $languageCode;

    /** @var string */
    private $id;

    public function __construct(string $languageCode, string $id)
    {
        $this->languageCode = $languageCode;
        $this->id = $id;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getReferenceId(): string
    {
        return $this->id;
    }
}
