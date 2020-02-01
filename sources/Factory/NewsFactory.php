<?php

declare(strict_types=1);

namespace Ciebit\News\Factory;

use Ciebit\News\News;
use Ciebit\News\Languages\Collection as LanguageCollection;
use Ciebit\News\Status;
use DateTime;

class NewsFactory
{
    private string $authorId;
    private string $body;
    private string $coverId;
    private DateTime $dateTime;
    private string $id;
    private array $labelsId;
    private string $language;
    private LanguageCollection $languageReferences;
    private string $slug;
    private Status $status;
    private string $summary;
    private string $title;
    private int $views;

    public function __construct()
    {
        $this->authorId = '';
        $this->body = '';
        $this->coverId = '';
        $this->dateTime = new DateTime;
        $this->id = '';
        $this->labelsId = [];
        $this->language = 'en';
        $this->languageReferences = new LanguageCollection;
        $this->title = '';
        $this->status = Status::DRAFT();
        $this->slug = '';
        $this->summary = '';
        $this->views = 0;
    }

    public function create(): News
    {
        return new News(
            $this->title,
            $this->summary,
            $this->body,
            $this->slug,
            $this->dateTime,
            $this->language,
            $this->languageReferences,
            $this->status,
            $this->coverId,
            $this->authorId,
            $this->views,
            $this->labelsId,
            $this->id,
        );
    }

    public function setAuthorId(string $id): self
    {
        $this->authorId = $id;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function setCoverId(string $id): self
    {
        $this->coverId = $id;
        return $this;
    }

    public function setDateTime(DateTime $dataTime): self
    {
        $this->dateTime = $dataTime;
        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setLabelsId(string ...$ids): self
    {
        $this->labelsId = $ids;
        return $this;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function setLanguageReferences(LanguageCollection $languageReferences): self
    {
        $this->languageReferences = $languageReferences;
        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }
    
    public function setNews(News $news): self
    {
        $this->setAuthorId($news->getAuthorId())
            ->setBody($news->getBody())
            ->setCoverId($news->getCoverId())
            ->setDateTime($news->getDateTime())
            ->setId($news->getId())
            ->setLabelsId(...$news->getLabelsId())
            ->setLanguage($news->getLanguage())
            ->setLanguageReferences($news->getLanguageReferences())
            ->setSlug($news->getSlug())
            ->setStatus($news->getStatus())
            ->setSummary($news->getSummary())
            ->setTitle($news->getTitle())
            ->setViews($news->getViews());

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;
        return $this;
    }
}