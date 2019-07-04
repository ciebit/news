<?php
namespace Ciebit\News;

use DateTime;
use Ciebit\News\Languages\Collection as LanguagesCollection;
use Ciebit\News\Languages\Reference as LanguagesReference;
use Ciebit\News\Status;

class News
{
    /** @var string */
    private $authorId;

    /** @var string */
    private $body;

    /** @var string */
    private $coverId;

    /** @var DateTime */
    private $dateTime;

    /** @var string */
    private $id;

    /** @var array */
    private $labelsId;

    /** @var string */
    private $language;

    /** @var LanguagesCollection */
    private $languageReferences;

    /** @var string */
    private $slug;

    /** @var Status */
    private $status;

    /** @var string */
    private $summary;

    /** @var string */
    private $title;

    /** @var int */
    private $views;

    public function __construct(string $title, Status $status)
    {
        $this->authorId = '';
        $this->body = '';
        $this->coverId = '';
        $this->dateTime = new DateTime;
        $this->id = '';
        $this->labelsId = [];
        $this->language = 'pt-br';
        $this->languageReferences = new LanguagesCollection;
        $this->slug = '';
        $this->status = $status;
        $this->summary = '';
        $this->title = $title;
        $this->views = 0;
    }

    public function addLanguageReference(LanguagesReference ...$languageReference): self
    {
        $this->languageReferences->add(...$languageReference);
        return $this;
    }

    /**
     * GETs
     */
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
        return $this->dateTime;
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
        return $this->languageReferences;
    }


    /**
     * SETs
    */
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

    public function setDateTime(DateTime $dateTime): self
    {
        $this->dateTime = $dateTime;
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

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;
        $this->valid();
        return $this;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    public function setViews(int $total): self
    {
        $this->views = $total;
        return $this;
    }
}
