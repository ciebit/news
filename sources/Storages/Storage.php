<?php
namespace Ciebit\News\Storages;

use Ciebit\News\Collection;
use Ciebit\News\News;
use Ciebit\News\Status;

interface Storage
{
    /** @var string */
    public const FIELD_AUTHOR_ID = 'author_id';

    /** @var string */
    public const FIELD_BODY = 'body';

    /** @var string */
    public const FIELD_COVER_ID = 'cover_id';

    /** @var string */
    public const FIELD_DATETIME = 'datetime';

    /** @var string */
    public const FIELD_ID = 'id';

    /** @var string */
    public const FIELD_LABEL_ID = 'id';

    /** @var string */
    public const FIELD_LABEL_LABEL_ID = 'label_id';

    /** @var string */
    public const FIELD_LABEL_NEWS_ID = 'news_id';

    /** @var string */
    public const FIELD_LANGUAGE = 'language';

    /** @var string */
    public const FIELD_LANGUAGES_REFERENCES = 'languages_references';

    /** @var string */
    public const FIELD_SLUG = 'slug';

    /** @var string */
    public const FIELD_STATUS = 'status';

    /** @var string */
    public const FIELD_SUMMARY = 'summary';

    /** @var string */
    public const FIELD_TITLE = 'title';

    /** @var string */
    public const FIELD_VIEWS = 'views';

    public function addFilterByBody(string $operator, string ...$body): self;

    public function addFilterById(string $operator, string ...$id): self;

    public function addFilterByLabelId(string $operator, string ...$id): self;

    public function addFilterByLanguage(string $operator, string ...$language): self;

    public function addFilterBySlug(string $operator, string ...$slug): self;

    public function addFilterByStatus(string $operator, Status ...$status): self;

    public function addFilterByTitle(string $operator, string ...$title): self;

    /** @throws Execption */
    public function destroy(News $news): self;

    /** @throws Execption */
    public function findAll(): Collection;

    /** @throws Execption */
    public function findOne(): ?News;

    public function getTotalItemsOfLastFindWithoutLimit(): int;

    public function setLimit(int $limit): self;

    public function setOffset(int $offset): self;

    /** @throws Execption */
    public function store(News $new): self;

    /** @throws Execption */
    public function update(News $new): self;
}
