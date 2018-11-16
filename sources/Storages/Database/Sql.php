<?php
declare(strict_types=1);
namespace Ciebit\News\Storages\Database;

use Ciebit\Labels\Collection as LabelsCollection;
use Ciebit\Labels\Storages\Storage as LabelStorage;
use Ciebit\Stories\Storages\Storage as StoryStorage;
use Ciebit\Stories\Story;
use Ciebit\Files\Images\Image;
use Ciebit\Files\Storages\Storage as FilesStorage;
use Ciebit\News\Collection;
use Ciebit\News\Builders\FromArray as Builder;
use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\News\Storages\Storage;
use Ciebit\News\Storages\Database\Database;
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
    private $storyStorage; # StoryStorage
    private $table; # string
    private $tableLabelAssociation; # string
    private $tableStory; # string

    public function __construct(
        PDO $pdo,
        FilesStorage $filesStorage,
        StoryStorage $storyStorage,
        LabelStorage $labelStorage
    ) {
        $this->filesStorage = $filesStorage;
        $this->labelStorage = $labelStorage;
        $this->pdo = $pdo;
        $this->storyStorage = $storyStorage;
        $this->table = 'cb_news';
        $this->tableLabelAssociation = 'cb_news_labels';
    }

    public function addFilterById(string $operator, int ...$ids): Storage
    {
        $field = '`news`.`id`';

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

    public function addFilterByLabelId(string $operator, int $id): Storage
    {
        $key = 'label_id';
        $sql = "`labels_ass`.`label_id` $operator :{$key}";
        $this->addSqlJoin(
            "INNER JOIN `{$this->tableLabelAssociation}` AS `labels_ass`
            ON `labels_ass`.`news_id` = `news`.`id`"
        )->addfilter($key, $sql, PDO::PARAM_INT, $id);
        return $this;
    }

    public function addFilterByStoryId(string $operator, string $id): Storage
    {
        $key = 'story_id';
        $sql = "`news`.`story_id` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_INT, $id);
        $this->localFilter = true;
        return $this;
    }

    public function addFilterByStatus(string $operator, Status $status): Storage
    {
        $key = 'status';
        $sql = "`news`.`status` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_INT, $status->getValue());
        $this->localFilter = true;
        return $this;
    }

    public function createNews(
        array $newsData,
        Story $story,
        ?Image $cover,
        LabelsCollection $labels
    ): News {
        $status = new Status((int) $newsData['status']);
        $news = new News($story, $status);
        $news->setId($newsData['id'])
        ->setLabels($labels);

        if ($cover instanceof Image) {
            $news->setImage($cover);
        }

        return $news;
    }

    public function get(): ?News
    {
        $newsData = $this->getNewsData();
        if (is_null($newsData)) {
            return null;
        }

        $storyStorage = clone $this->storyStorage;
        $storyStorage->addFilterById('=', (int) $newsData['story_id']);
        $story = $storyStorage->get();

        $cover = null;
        if (! is_null($newsData['cover_id'])) {
            $filesStorage = clone $this->filesStorage;
            $filesStorage->addFilterById((int) $newsData['cover_id']);
            $cover = $filesStorage->get();
        }

        $labels = $this->getLabelsByNewsId($newsData['id']);

        return $this->createNews($newsData, $story, $cover, $labels);
    }

    /**
     * @throw Exception
    */
    private function getNewsData(): ?array
    {
        $statement = $this->pdo->prepare("
            SELECT
            {$this->getFields()}
            FROM {$this->table} as `news`
            {$this->generateSqlJoin()}
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            LIMIT 1
        ");

        $this->bind($statement);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.get_error', 2);
        }

        $newsData = $statement->fetch(PDO::FETCH_ASSOC);

        if ($newsData == false) {
            return null;
        }

        return $newsData;
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
        $ids = array_map('intval', $ids);

        $labelsStorage = clone $this->labelStorage;
        $labelsStorage->addFilterByIds('=', ...$ids);
        return $labelsStorage->getAll();
    }

    public function getAll(): Collection
    {
        $statement = $this->pdo->prepare(
            "SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->table} as `news`
            {$this->generateSqlJoin()}
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            {$this->generateSqlLimit()}"
        );

        $this->bind($statement);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.get_error', 2);
        }

        $collection = new Collection;
        $newsData = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($newsData) < 1) {
            return $collection;
        }

        $storiesId = array_column($newsData, 'story_id');
        $storiesId = array_map('intval', $storiesId);
        $storyStorage = clone $this->storyStorage;
        $storyCollection = $storyStorage->addFilterById('=', ...$storiesId)->getAll();

        $coversId = array_column($newsData, 'cover_id');
        $coversId = array_map('intval', $coversId);
        $filesStorage = clone $this->filesStorage;
        $fileCollection = $filesStorage->addFilterByIds('=', ...$coversId)->getAll();

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
            $labelsIds = array_map('intval', $labelsIds);
            $labelStorage = clone $this->labelStorage;
            $labelStorage->addFilterByIds('IN', ...$labelsIds);
            $labelCollection = $labelStorage->getAll();
        }

        foreach ($newsData as $newsItemData) {
            $labels = new LabelsCollection;

            foreach ($labelsAndNewsAssossiation as $assossiation) {
                if ($assossiation['news_id'] != $newsItemData['id']) {
                    continue;
                }
                $labels->add($labelCollection->getById((int) $assossiation['label_id']));
            }

            $collection->add(
                $this->createNews(
                    $newsItemData,
                    $storyCollection->getById($newsItemData['story_id']),
                    $fileCollection->getById((int) $newsItemData['cover_id']),
                    $labels
                )
            );
        }

        return $collection;
    }

    private function getFields(): string
    {
        return '
            `news`.`id`,
            `news`.`story_id`,
            `news`.`cover_id`,
            `news`.`status`
        ';
    }

    private function getLabels(array $ids): LabelsCollection
    {
        $ids = array_map('intval', $ids);
        $labelStorage = clone $this->labelStorage;
        $labelStorage->addFilterByIds('=', ...$ids);
        return $labelStorage->getAll();
    }

    public function getTotalRows(): int
    {
        return (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
    }

    public function setStartingLine(int $lineInit): Storage
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

    public function setTotalLines(int $total): Storage
    {
        parent::setLimit($total);
        return $this;
    }

    /**
     * @throw Exception
    */
    public function update(News $news): Storage
    {
        $this->storyStorage->update($news->getStory());
        return $this;
    }
}
