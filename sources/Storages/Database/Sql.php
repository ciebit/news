<?php
namespace Ciebit\News\Storages\Database;

use Ciebit\News\Builders\Builder;
use Ciebit\News\Collection;
use Ciebit\News\Languages\Reference as LanguageReference;
use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\News\Storages\Database\Database;
use Ciebit\News\Storages\Storage;
use Ciebit\SqlHelper\Sql as SqlHelper;
use DateTime;
use Exception;
use PDO;
use PDOStatement;

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
    private const COLUMN_LABEL_IDS = 'label_ids';
    
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

    private function bindValuesStoreAndUpdate(PDOStatement $statement, News $news): self
    {
        $languageReferences = null;
        if ($news->getLanguageReferences()->count() > 0) {
            $languageReferences = json_encode($news->getLanguageReferences(), JSON_FORCE_OBJECT);
        }

        $statement->bindValue(':authorId', $news->getAuthorId() ?: null, PDO::PARAM_INT);
        $statement->bindValue(':body', $news->getBody() ?: null, PDO::PARAM_STR);
        $statement->bindValue(':coverId', $news->getCoverId() ?: null, PDO::PARAM_INT);
        $statement->bindValue(':dateTime', $news->getDateTime()->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $statement->bindValue(':language', $news->getLanguage() ?: null, PDO::PARAM_STR);
        $statement->bindValue(':languageReferences', $languageReferences, PDO::PARAM_STR);
        $statement->bindValue(':slug', $news->getSlug() ?: null, PDO::PARAM_STR);
        $statement->bindValue(':status', $news->getStatus()->getValue(), PDO::PARAM_INT);
        $statement->bindValue(':summary', $news->getSummary() ?: null, PDO::PARAM_STR);
        $statement->bindValue(':title', $news->getTitle() ?: null, PDO::PARAM_STR);
        $statement->bindValue(':views', $news->getViews(), PDO::PARAM_INT);

        return $this;
    }

    private function createNews(array $newsData): News
    {
        return Builder::build([
            'authorId' => $newsData[self::COLUMN_AUTHOR_ID],
            'body' => $newsData[self::COLUMN_BODY],
            'coverId' => $newsData[self::COLUMN_COVER_ID],
            'dateTime' => $newsData[self::COLUMN_DATETIME],
            'id' => $newsData[self::COLUMN_ID],
            'language' => $newsData[self::COLUMN_LANGUAGE],
            'languagesReferences' => $newsData[self::COLUMN_LANGUAGES_REFERENCES],
            'labelsId' => $newsData[self::COLUMN_LABEL_IDS] ? explode(',', $newsData[self::COLUMN_LABEL_IDS]) : null,
            'slug' => $newsData[self::COLUMN_SLUG],
            'status' => $newsData[self::COLUMN_STATUS],
            'summary' => $newsData[self::COLUMN_SUMMARY],
            'title' => $newsData[self::COLUMN_TITLE],
            'views' => $newsData[self::COLUMN_VIEWS],
        ]);
    }

    /**
     * @throws Exception
     */
    private function destroyLabels(string $newsId): self
    {
        $fieldNewsId = self::COLUMN_LABEL_NEWS_ID;

        $statement = $this->pdo->prepare(
            "DELETE FROM {$this-> tableLabelAssociation} WHERE `{$fieldNewsId}` = :id"
        );

        $statement->bindValue(':id', $newsId, PDO::PARAM_INT);

        if (!$statement->execute()) {
            throw new Exception('ciebit.news.storages.database.destroy', 5);
        }

        return $this;
    }

    /**
     * @throws Exception
    */
    public function findAll(): Collection
    {
        $fieldId = self::COLUMN_ID;
        $fieldNewsId = self::COLUMN_LABEL_NEWS_ID;
        $fieldLabelId = self::COLUMN_LABEL_LABEL_ID;
        $fieldLabelIds = self::COLUMN_LABEL_IDS;

        $statement = $this->pdo->prepare(
            "SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()},
            (
                SELECT GROUP_CONCAT(`{$this->tableLabelAssociation}`.`{$fieldLabelId}`)
                FROM  `{$this->tableLabelAssociation}`
                WHERE `{$this->tableLabelAssociation}`.`{$fieldNewsId}` = `{$this->table}`.`{$fieldId}`
            )  as `{$fieldLabelIds}`
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

    /**
     * @throws Exception
     */
    public function store(News $news): Storage
    {
        $fields = implode('`,`', [
            self::COLUMN_AUTHOR_ID,
            self::COLUMN_BODY,
            self::COLUMN_COVER_ID,
            self::COLUMN_DATETIME,
            self::COLUMN_LANGUAGE,
            self::COLUMN_LANGUAGES_REFERENCES,
            self::COLUMN_SLUG,
            self::COLUMN_STATUS,
            self::COLUMN_SUMMARY,
            self::COLUMN_TITLE,
            self::COLUMN_VIEWS,
        ]);

        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->table}
            (`{$fields}`)
            VALUES
            (
                :authorId, :body, :coverId, :dateTime, 
                :language, :languageReferences, 
                :slug, :status, :summary, 
                :title, :views
            )"
        );

        $this->bindValuesStoreAndUpdate($statement, $news);

        $this->pdo->beginTransaction();

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.store_error', 3);
        }

        $id = $this->pdo->lastInsertId();
        if (! empty($news->getLabelsId())) {
            $this->storeLabels($id, ...$news->getLabelsId());
        }

        $this->pdo->commit();

        $news->setId($id);

        return $this;
    }

    private function storeLabels(string $newsId, string ...$labelIds): self
    {
        $fields = implode('`,`', [
            self::COLUMN_LABEL_NEWS_ID,
            self::COLUMN_LABEL_LABEL_ID
        ]);

        $values = [];

        foreach ($labelIds as $key => $labelId ) {
            $values[] = "(:newsId, :labelId{$key})";
        }

        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->tableLabelAssociation} (`{$fields}`) 
            VALUES ". implode(',', $values)
        );

        $statement->bindValue(':newsId', $newsId, PDO::PARAM_INT);

        foreach ($labelIds as $key => $labelId) {
            $statement->bindValue(":labelId{$key}", $labelId, PDO::PARAM_INT);
        }

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.store_labels_error', 4);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function update(News $news): Storage
    {
        $fieldAuthorId = self::COLUMN_AUTHOR_ID;
        $fieldBody = self::COLUMN_BODY;
        $fieldCoverId = self::COLUMN_COVER_ID;
        $fieldDateTime = self::COLUMN_DATETIME;
        $fieldId = self::COLUMN_ID;
        $fieldLanguage = self::COLUMN_LANGUAGE;
        $fieldLanguagesReferences = self::COLUMN_LANGUAGES_REFERENCES;
        $fieldSlug = self::COLUMN_SLUG;
        $fieldStatus = self::COLUMN_STATUS;
        $fieldSummary = self::COLUMN_SUMMARY;
        $fieldTitle = self::COLUMN_TITLE;
        $fieldViews = self::COLUMN_VIEWS;

        $statement = $this->pdo->prepare(
            "UPDATE {$this->table} SET
                {$fieldAuthorId} = :authorId, 
                {$fieldBody} = :body, 
                {$fieldCoverId} = :coverId, 
                {$fieldDateTime} = :dateTime, 
                {$fieldLanguage} = :language, 
                {$fieldLanguagesReferences} = :languageReferences, 
                {$fieldSlug} =:slug, 
                {$fieldStatus} = :status, 
                {$fieldSummary} = :summary, 
                {$fieldTitle} = :title, 
                {$fieldViews} = :views
            WHERE {$fieldId} = :id
            LIMIT 1"
        );

        $statement->bindValue(':id', $news->getId() ?: null, PDO::PARAM_INT);
        $this->bindValuesStoreAndUpdate($statement, $news);

        $this->pdo->beginTransaction();

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.update_error', 4);
        }

        $this->destroyLabels($news->getId());

        if (! empty($news->getLabelsId())) {
            $this->storeLabels($news->getId(), ...$news->getLabelsId());
        }

        $this->pdo->commit();

        return $this;
    }

    private function updateTotalItemsWithoutLimit(): self
    {
        $this->totalItemsOfLastFindWithoutLimit = $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return $this;
    }
}
