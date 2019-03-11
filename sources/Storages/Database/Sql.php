<?php
namespace Ciebit\News\Storages\Database;

use Ciebit\Labels\Collection as LabelsCollection;
use Ciebit\Labels\Storages\Storage as LabelStorage;
use Ciebit\Files\Collection as FilesCollection;
use Ciebit\Files\Images\Image;
use Ciebit\Files\Storages\Storage as FilesStorage;
use Ciebit\News\Collection;
use Ciebit\News\Builders\FromArray as Builder;
use Ciebit\News\LanguageReference;
use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\News\Storages\Database\Database;
use DateTime;
use Exception;
use PDO;

use function array_column;
use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function count;
use function explode;
use function is_aray;
use function intval;

class Sql extends SqlFilters implements Database
{
    private $filesStorage; # FilesStorage
    private $labelStorage; # LabelStorage
    private $pdo; # PDO
    private $table; # string
    private $tableLabelAssociation; # string
    private $totalItemsLastQuery; # int

    public function __construct(
        PDO $pdo,
        FilesStorage $filesStorage,
        LabelStorage $labelStorage
    ) {
        $this->filesStorage = $filesStorage;
        $this->labelStorage = $labelStorage;
        $this->pdo = $pdo;
        $this->table = 'cb_news';
        $this->tableLabelAssociation = 'cb_news_labels';
        $this->totalItemsLastQuery = 0;
    }

    public function addFilterByBody(string $operator, string $body): Database
    {
        $key = $this->generateValueKey();
        $field = "`{$this->table}`.`body`";
        $sql = "{$field} {$operator} :{$key}";

        $this->addfilter($key, $sql, PDO::PARAM_STR, $body);

        return $this;
    }

    public function addFilterById(string $operator, string ...$ids): Database
    {
        $field = "`{$this->table}`.`id`";

        if (count($ids) == 1) {
            $key = $this->generateValueKey();
            $sql = "{$field} $operator :{$key}";
            $this->addfilter($key, $sql, PDO::PARAM_INT, $ids[0]);
            return $this;
        }

         $keys = [];
         foreach ($ids as $id) {
             $key = $this->generateValueKey();
             $this->addBind($key, PDO::PARAM_INT, $id);
             $keys[] = $key;
         }

         $keysSql = implode(', :', $keys);
         $operator = str_replace(['=', '!='], ['IN', 'NOT IN'], $operator);
         $this->addSqlFilter("{$field} {$operator} (:{$keysSql})");
         return $this;
    }

    public function addFilterByLabelId(string $operator, int $id): Database
    {
        $key = $this->generateValueKey();
        $sql = "`{$this->tableLabelAssociation}`.`label_id` $operator :{$key}";
        $this->addSqlJoin(
            "INNER JOIN `{$this->tableLabelAssociation}`
            ON `$this->tableLabelAssociation`.`news_id` = `{$this->table}`.`id`"
        )->addfilter($key, $sql, PDO::PARAM_INT, $id);
        return $this;
    }

    public function addFilterByLanguage(string $operator, string $language): Database
    {
        $key = $this->generateValueKey();
        $field = "`{$this->table}`.`language`";
        $sql = "{$field} {$operator} :{$key}";

        $this->addfilter($key, $sql, PDO::PARAM_STR, $language);

        return $this;
    }

    public function addFilterByStatus(string $operator, Status $status): Database
    {
        $key = $this->generateValueKey();
        $sql = "`{$this->table}`.`status` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_INT, $status->getValue());
        $this->localFilter = true;
        return $this;
    }

    public function addFilterByTitle(string $operator, string $title): Database
    {
        $key = $this->generateValueKey();
        $field = "`{$this->table}`.`title`";
        $sql = "{$field} {$operator} :{$key}";

        $this->addfilter($key, $sql, PDO::PARAM_STR, $title);

        return $this;
    }

    public function addFilterByUri(string $operator, string $uri): Database
    {
        $key = $this->generateValueKey();
        $field = "`{$this->table}`.`uri`";
        $sql = "{$field} {$operator} :{$key}";

        $this->addfilter($key, $sql, PDO::PARAM_STR, $uri);

        return $this;
    }

    public function createNews(
        array $newsData,
        ?Image $cover,
        LabelsCollection $labels
    ): News {
        $status = new Status((int) $newsData['status']);
        $news = new News($newsData['title'], $status);
        $news->setId($newsData['id'])
        ->setBody((string) $newsData['body'])
        ->setSummary((string) $newsData['summary'])
        ->setUri((string) $newsData['uri'])
        ->setViews((int) $newsData['views'])
        ->setLanguage((string) $newsData['language'])
        ->setLabels($labels);

        if ($newsData['datetime'] != null) {
            $news->setDateTime(new DateTime($newsData['datetime']));
        }

        if ($newsData['languages_references'] != null) {
            $languageReferences = json_decode($newsData['languages_references'], true);
            foreach ($languageReferences as $languageCode => $id) {
                $news->addLanguageReference(new LanguageReference($languageCode, $id));
            }
        }

        if ($cover instanceof Image) {
            $news->setCover($cover);
        }

        return $news;
    }

    /**
     * @throw Exception
    */
    public function get(): ?News
    {
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->table}
            {$this->generateSqlJoin()}
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            LIMIT 1
        ");

        $this->bind($statement);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.get_error', 2);
        }

        $this->totalItemsLastQuery = (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();

        $newsData = $statement->fetch(PDO::FETCH_ASSOC);

        if ($newsData == false) {
            return null;
        }

        $cover = null;
        if ($newsData['cover_id'] != null) {
            $filesStorage = clone $this->filesStorage;
            $filesStorage->addFilterById('=', $newsData['cover_id']);
            $cover = $filesStorage->findOne();
        }

        $labels = $this->getLabelsByNewsId($newsData['id']);

        return $this->createNews($newsData, $cover, $labels);
    }

    /**
     * @throw Exception
    */
    private function getLabelsByNewsId(string $id): LabelsCollection
    {
        $statement = $this->pdo->prepare(
            "SELECT `id`, `label_id`, `news_id`
            FROM {$this->tableLabelAssociation}
            WHERE news_id = :id"
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.get_error', 2);
        }

        $labelsData = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($labelsData) < 1) {
            return new LabelsCollection;
        }

        $ids = array_column($labelsData, 'label_id');

        $labelsStorage = clone $this->labelStorage;
        $labelsStorage->addFilterById('=', ...$ids);
        return $labelsStorage->findAll();
    }

    public function getAll(): Collection
    {
        $statement = $this->pdo->prepare(
            "SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->table}
            {$this->generateSqlJoin()}
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            {$this->generateSqlLimit()}"
        );

        $this->bind($statement);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.get_error', 2);
        }

        $this->totalItemsLastQuery = (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();

        $collection = new Collection;
        $newsData = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($newsData) < 1) {
            return $collection;
        }

        $coversId = array_filter(array_column($newsData, 'cover_id'));
        $fileCollection = new FilesCollection;
        if (! empty($coversId)) {
            $filesStorage = clone $this->filesStorage;
            $fileCollection = $filesStorage->addFilterById('=', ...$coversId)->findAll();
        }

        $newsId = array_column($newsData, 'id');
        $sqlNewsId = implode(',', $newsId);
        $statementLabels = $this->pdo->query(
            "SELECT `news_id`, `label_id`
            FROM {$this->tableLabelAssociation}
            WHERE `news_id` IN ({$sqlNewsId})"
        );
        $labelsAndNewsAssossiation = $statementLabels->fetchAll(PDO::FETCH_ASSOC);
        $labelCollection = new LabelsCollection;
        if (count($labelsAndNewsAssossiation) > 0) {
            $labelsIds = array_column($labelsAndNewsAssossiation, 'label_id');
            $labelsIds = array_unique($labelsIds);
            $labelStorage = clone $this->labelStorage;
            $labelStorage->addFilterById('=', ...$labelsIds);
            $labelCollection = $labelStorage->findAll();
        }

        foreach ($newsData as $newsItemData) {
            $labels = new LabelsCollection;

            foreach ($labelsAndNewsAssossiation as $assossiation) {
                if ($assossiation['news_id'] != $newsItemData['id']) {
                    continue;
                }
                $labels->add($labelCollection->getById((int) $assossiation['label_id']));
            }

            $file = null;
            if (! empty($newsItemData['cover_id'])) {
                $file = $fileCollection->getById($newsItemData['cover_id']);
            }

            $collection->add(
                $this->createNews(
                    $newsItemData,
                    $file,
                    $labels
                )
            );
        }

        return $collection;
    }

    private function getFields(): string
    {
        return "
            `{$this->table}`.`id`,
            `{$this->table}`.`cover_id`,
            `{$this->table}`.`title`,
            `{$this->table}`.`summary`,
            `{$this->table}`.`body`,
            `{$this->table}`.`datetime`,
            `{$this->table}`.`uri`,
            `{$this->table}`.`views`,
            `{$this->table}`.`language`,
            `{$this->table}`.`languages_references`,
            `{$this->table}`.`status`
        ";
    }

    private function getLabels(array $ids): LabelsCollection
    {
        $ids = array_map('intval', $ids);
        $labelStorage = clone $this->labelStorage;
        $labelStorage->addFilterByIds('=', ...$ids);
        return $labelStorage->getAll();
    }

    public function getTotalItems(): int
    {
        return $this->totalItemsLastQuery;
    }

    public function setStartingItem(int $lineInit): Database
    {
        parent::setOffset($lineInit);
        return $this;
    }

    public function setTableLabelAssociation(string $name): Database
    {
        $this->tableLabelAssociation = $name;
        return $this;
    }

    public function setTable(string $name): Database
    {
        $this->table = $name;
        return $this;
    }

    public function setTotalItems(int $total): Database
    {
        parent::setLimit($total);
        return $this;
    }
}
