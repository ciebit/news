<?php

declare(strict_types=1);

namespace Ciebit\News;

use Ciebit\News\Languages\Collection as LanguagesCollection;
use Ciebit\News\Languages\Reference as LanguagesReference;
use Ciebit\News\Status;
use DateTime;
use JsonSerializable;

use function array_map;
use function strval;

class News implements JsonSerializable
{
    private string $authorId;
    private string $body;
    private string $coverId;
    private DateTime $dateTime;
    private string $id;
    private array $labelsId;
    private string $language;
    private LanguagesCollection $languageReferences;
    private string $slug;
    private Status $status;
    private string $summary;
    private string $title;
    private int $views;

    public function __construct(
        string $title, 
        string $summary, 
        string $body, 
        string $slug, 
        DateTime $dateTime,
        string $language, 
        LanguagesCollection $languageReferences, 
        Status $status,
        string $coverId = '',
        string $authorId = '',
        int $views = 0,
        array $labelsId = [],
        string $id = ''
    ) {
        $this->authorId = $authorId;
        $this->body = $body;
        $this->coverId = $coverId;
        $this->dateTime = $dateTime;
        $this->id = $id;
        $this->labelsId = array_map('strval', $labelsId);
        $this->language = $language;
        $this->languageReferences = $languageReferences;
        $this->slug = $slug;
        $this->status = $status;
        $this->summary = $summary;
        $this->title = $title;
        $this->views = $views;
    }
    
    public function getAuthorId(): string
    {
        return $this->authorId;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getCoverId(): string
    {
        return $this->coverId;
    }

    public function getDateTime(): DateTime
    {
        return clone $this->dateTime;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabelsId(): array
    {
        return $this->labelsId;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getLanguageReferences(): LanguagesCollection
    {
        return clone $this->languageReferences;
    }

    public function jsonSerialize(): array
    {
        return [
            'authorId' => $this->getAuthorId(),
            'body' => $this->getBody(),
            'coverId' => $this->getCoverId(),
            'dateTime' => $this->getDateTime()->format('Y-m-d H:i:s'),
            'id' => $this->getId(),
            'labelsId' => $this->getLabelsId(),
            'language' => $this->getLanguage(),
            'languageReferences' => $this->getLanguageReferences(),
            'slug' => $this->getSlug(),
            'status' => $this->getStatus(),
            'summary' => $this->getSummary(),
            'title' => $this->getTitle(),
            'views' => $this->getViews(),
        ];
    }
}
