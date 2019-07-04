<?php
namespace Ciebit\News\Storages\Database;

use Ciebit\News\Collection;
use Ciebit\News\LanguageReference;
use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\News\Storages\Database\Database;
use Ciebit\News\Storages\Storage;
use Ciebit\SqlHelper\Sql as SqlHelper;
use DateTime;
use Exception;
use PDO;

use function array_map;
use function count;
use function intval;

class Sql implements Database
{
    /** @var string */
    private const COLUMN_AUTHOR_ID = 'author_id';

    /** @var string */
    private const COLUMN_BODY = 'body';

    /** @var string */
    private const COLUMN_COVER_ID = 'cover_id';

    /** @var string */
    private const COLUMN_DATETIME = 'datetime';

    /** @var string */
    private const COLUMN_ID = 'id';

    /** @var string */
    private const COLUMN_LABEL_ID = 'id';

    /** @var string */
    private const COLUMN_LABEL_LABEL_ID = 'label_id';

    /** @var string */
    private const COLUMN_LABEL_NEWS_ID = 'news_id';

    /** @var string */
    private const COLUMN_LANGUAGE = 'language';

    /** @var string */
    private const COLUMN_LANGUAGES_REFERENCES = 'languages_references';

    /** @var string */
    private const COLUMN_SLUG = 'slug';

    /** @var string */
    private const COLUMN_STATUS = 'status';

    /** @var string */
    private const COLUMN_SUMMARY = 'summary';

    /** @var string */
    private const COLUMN_TITLE = 'title';

    /** @var string */
    private const COLUMN_VIEWS = 'views';

    /** @var PDO */
    private $pdo;

    /** @var SqlHelper */
    private $sqlHelper;

    /** @var string */
    private $table;

    /** @var string */
    private $tableLabelAssociation;

    /** @var int */
    private $totalItemsOfLastFindWithoutLimit;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->sqlHelper = new SqlHelper;
        $this->table = 'cb_news';
        $this->tableLabelAssociation = 'cb_news_labels';
        $this->totalItemsOfLastFindWithoutLimit = 0;
    }

    public function __clone()
    {
        $this->sqlHelper = clone $this->sqlHelper;
    }

    private function addFilter(string $fieldName, int $type, string $operator, ...$value): self
    {
        $field = "`{$this->table}`.`{$fieldName}`";
        $this->sqlHelper->addFilterBy($field, $type, $operator, ...$value);
        return $this;
    }

    public function addFilterByAuthorId(string $operator, string ...$id): Storage
    {
        $id = array_map('intval', $id);
        $this->addFilter(self::COLUMN_AUTHOR_ID, PDO::PARAM_INT, $operator, ...$id);
        return $this;
    }

    public function addFilterByBody(string $operator, string ...$body): Storage
    {
        $this->addFilter(self::COLUMN_BODY, PDO::PARAM_STR, $operator, ...$body);
        return $this;
    }

    public function addFilterById(string $operator, string ...$id): Storage
    {
        $id = array_map('intval', $id);
        $this->addFilter(self::COLUMN_ID, PDO::PARAM_INT, $operator, ...$id);
        return $this;
    }

    public function addFilterByLabelId(string $operator, string ...$id): Storage
    {
        $tableAssociation = "`{$this->tableLabelAssociation}`";
        $fieldLabelId = '`'. self::COLUMN_LABEL_LABEL_ID .'`';
        $fieldNewsId = '`'. self::COLUMN_LABEL_NEWS_ID .'`';
        $fieldId = '`'. self::COLUMN_ID .'`';

        $ids = array_map('intval', $id);
        $this->sqlHelper->addFilterBy("{$tableAssociation}.{$fieldLabelId}", PDO::PARAM_INT, $operator, ...$id);
        $this->sqlHelper->addSqlJoin(
            "INNER JOIN {$this->tableLabelAssociation}
            ON {$this->tableLabelAssociation}.{$fieldNewsId} = {$this->table}.{$fieldId}"
        );

        return $this;
    }

    public function addFilterByLanguage(string $operator, string ...$language): Storage
    {
        $this->addFilter(self::COLUMN_LANGUAGE, PDO::PARAM_STR, $operator, ...$language);
        return $this;
    }

    public function addFilterBySlug(string $operator, string ...$slug): Storage
    {
        $this->addFilter(self::COLUMN_SLUG, PDO::PARAM_STR, $operator, ...$slug);
        return $this;
    }

    public function addFilterByStatus(string $operator, Status ...$status): Storage
    {
        $statusInt = array_map(function($status){
            return (int) $status->getValue();
        }, $status);
        $this->addFilter(self::COLUMN_STATUS, PDO::PARAM_INT, $operator, ...$statusInt);
        return $this;
    }

    public function addFilterByTitle(string $operator, string ...$title): Storage
    {
        $this->addFilter(self::COLUMN_TITLE, PDO::PARAM_STR, $operator, ...$title);
        return $this;
    }

    public function addOrderBy(string $field, string $direction): Storage
    {
        $this->sqlHelper->addOrderBy($field, $direction);
        return $this;
    }

    public function createNews(array $newsData): News
    {
        $status = new Status((int) $newsData['status']);
        $news = new News($newsData['title'], $status);
        $news->setId($newsData['id'])
        ->setCoverId((string) $newsData['cover_id'])
        ->setAuthorId((string) $newsData['author_id'])
        ->setBody((string) $newsData['body'])
        ->setSummary((string) $newsData['summary'])
        ->setSlug((string) $newsData['slug'])
        ->setViews((int) $newsData['views'])
        ->setLanguage((string) $newsData['language'])
        ->setLabelsId(...explode(',', $newsData['labels_id']));

        if ($newsData['datetime'] != null) {
            $news->setDateTime(new DateTime($newsData['datetime']));
        }

        if ($newsData['languages_references'] != null) {
            $languageReferences = json_decode($newsData['languages_references'], true);
            foreach ($languageReferences as $languageCode => $id) {
                $news->addLanguageReference(new LanguageReference($languageCode, $id));
            }
        }

        return $news;
    }

    /**
     * @throws Exception
    */
    public function findAll(): Collection
    {
        $fieldId = self::COLUMN_ID;
        $fieldNewsId = self::COLUMN_LABEL_NEWS_ID;
        $fieldLabelId = self::COLUMN_LABEL_LABEL_ID;

        $statement = $this->pdo->prepare(
            "SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()},
            (
                SELECT GROUP_CONCAT(`{$this->tableLabelAssociation}`.`{$fieldLabelId}`)
                FROM  `{$this->tableLabelAssociation}`
                WHERE `{$this->tableLabelAssociation}`.`{$fieldNewsId}` = `{$this->table}`.`{$fieldId}`
            )  as `labels_id`
            FROM {$this->table}
            {$this->sqlHelper->generateSqlJoin()}
            WHERE {$this->sqlHelper->generateSqlFilters()}
            {$this->sqlHelper->generateSqlOrder()}
            {$this->sqlHelper->generateSqlLimit()}"
        );

        $this->sqlHelper->bind($statement);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.get_error', 2);
        }

        $this->updateTotalItemsWithoutLimit();

        $collection = new Collection;
        $newsData = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($newsData) < 1) {
            return $collection;
        }

        foreach ($newsData as $newsItemData) {
            $collection->add($this->createNews($newsItemData));
        }

        return $collection;
    }

    public function findOne(): ?News
    {
        $storage = clone $this;
        $newsCollection = $storage->setLimit(1)->findAll();

        if (count($newsCollection) == 0) {
            return null;
        }

        return $newsCollection->getArrayObject()->offsetGet(0);
    }

    private function getFields(): string
    {
        return "
            `{$this->table}`.`". self::COLUMN_ID ."`,
            `{$this->table}`.`". self::COLUMN_COVER_ID ."`,
            `{$this->table}`.`". self::COLUMN_AUTHOR_ID ."`,
            `{$this->table}`.`". self::COLUMN_TITLE ."`,
            `{$this->table}`.`". self::COLUMN_SUMMARY ."`,
            `{$this->table}`.`". self::COLUMN_BODY ."`,
            `{$this->table}`.`". self::COLUMN_DATETIME ."`,
            `{$this->table}`.`". self::COLUMN_SLUG ."`,
            `{$this->table}`.`". self::COLUMN_VIEWS ."`,
            `{$this->table}`.`". self::COLUMN_LANGUAGE ."`,
            `{$this->table}`.`". self::COLUMN_LANGUAGES_REFERENCES ."`,
            `{$this->table}`.`". self::COLUMN_STATUS ."`
        ";
    }

    public function getTotalItemsOfLastFindWithoutLimit(): int
    {
        return $this->totalItemsOfLastFindWithoutLimit;
    }

    public function setLimit(int $limit): Storage
    {
        $this->sqlHelper->setLimit($limit);
        return $this;
    }

    public function setOffset(int $offset): Storage
    {
        $this->sqlHelper->setOffset($offset);
        return $this;
    }

    public function setTable(string $name): Database
    {
        $this->table = $name;
        return $this;
    }

    public function setTableLabelAssociation(string $name): Database
    {
        $this->tableLabelAssociation = $name;
        return $this;
    }

    private function updateTotalItemsWithoutLimit(): self
    {
        $this->totalItemsOfLastFindWithoutLimit = $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return $this;
    }
}
