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
    static private $counterKey = 0;
    private $labelStorage; #LabelStorage
    private $localFilter; #bool
    private $pdo; #PDO
    private $storyStorage; #StoryStorage
    private $filesStorage; # FilesStorage
    private $tableLabel; #: string
    private $tableLabelAssociation; #: string
    private $tableGet; #string
    private $tableSave; #string
    private $table; #: string

    public function __construct(
        PDO $pdo,
        FilesStorage $filesStorage,
        StoryStorage $storyStorage,
        LabelStorage $labelStorage)
    {
        $this->localFilter = false;
        $this->pdo = $pdo;
        $this->filesStorage = $filesStorage;
        $this->labelStorage = $labelStorage;
        $this->storyStorage = $storyStorage;
        $this->tableGet = 'cb_news_complete';
        $this->tableLabel = 'cb_labels';
        $this->tableLabelAssociation = 'cb_news_labels';
        $this->tableSave = 'cb_news';
        $this->table = 'cb_news';
    }

    public function addFilterById(int $id, string $operator = '='): Storage
    {
        $key = 'id';
        $sql = "`news`.`id` $operator :{$key}";
        $this->addfilter($key, $sql, PDO::PARAM_INT, $id);
        $this->localFilter = true;
        return $this;
    }

    public function addFilterByIds(string $operator, int ...$ids): Storage
    {
         $keyPrefix = 'id';
         $keys = [];
         $operator = $operator == '!=' ? 'NOT IN' : 'IN';
         foreach ($ids as $id) {
             $key = $keyPrefix . self::$counterKey++;
             $this->addBind($key, PDO::PARAM_INT, $id);
             $keys[] = $key;
         }
         $keysStr = implode(', :', $keys);
         $this->addSqlFilter("`news`.`id` {$operator} (:{$keysStr})");
         $this->localFilter = true;
         return $this;
    }

    public function addFilterByLabelId(int $id, string $operator = '='): Storage
    {
        // $key = 'label_id';
        // $sql = "`labels`.`label_id` $operator :{$key}";
        // $this->addSqlJoin(
        //     "INNER JOIN `{$this->tableLabelAssociation}` AS `labels`
        //     ON `labels`.`news_id` = `news`.`id`"
        // )->addfilter($key, $sql, PDO::PARAM_INT, $id);
        return $this;
    }

    public function addFilterByLabelUri(string $uri, string $operator = '='): Storage
    {
        // $this->addSqlJoin(
        //     "INNER JOIN `{$this->tableLabelAssociation}`
        //     ON `{$this->tableLabelAssociation}`.`news_id` = `news`.`id`
        //     INNER JOIN `{$this->tableLabel}`
        //     ON `{$this->tableLabel}`.`id` = `{$this->tableLabelAssociation}`.`label_id`"
        // );

        // $key = 'label_uri';
        // $sql = "`{$this->tableLabel}`.`uri` $operator :{$key}";
        // $this->addfilter($key, $sql, PDO::PARAM_STR, $uri);

        return $this;
    }

    public function addFilterByStoryId(string $id, string $operator = '='): Storage
    {
        $key = 'story_id';
        $sql = "`news`.`story_id` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_INT, $id);
        $this->localFilter = true;
        return $this;
    }

    public function addFilterByStatus(Status $status, string $operator = '='): Storage
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
        if ($this->localFilter) {
            $newsData = $this->getNewsData();
            if (is_null($newsData)) {
                return null;
            }
            $this->getStoryStorage()->addFilterById((int) $newsData['story_id']);
            $story = $this->getStory();
        } else {
            $story = $this->getStory();
            $this->addFilterByStoryId((string) $story->getId());
            $newsData = $this->getNewsData();
        }

        $cover = $this->filesStorage->addFilterById((int) $newsData['cover_id'])->get();
        $labels = $this->getLabelsById($newsData['id']);

        return $this->createNews($newsData, $story, $cover, $labels);
    }

    private function getStory(): Story
    {
        return $this->getStoryStorage()->get();
    }

    /**
     * @throw Exception
    */
    private function getNewsData(): ?array
    {
        $statement = $this->pdo->prepare($sql="
            SELECT
            {$this->getFields()}
            FROM {$this->table} as `news`
            {$this->generateSqlJoin()}
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            LIMIT 1
        ");
        echo $sql;
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
    public function getLabelsById(string $id): LabelsCollection
    {
        $statement = $this->pdo->prepare("
            SELECT * FROM {$this->tableLabelAssociation} WHERE news_id = :id
        ");
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.news.storages.database.get_error', 2);
        }

        $labelsCollection = new LabelsCollection;
        $labelsData = $statement->fetchAll(PDO::FETCH_ASSOC);

        $ids = array_column($labelsData, 'label_id');
        $ids = array_map(function($id) {return (int) $id;}, $ids);

        $labelsStorage = clone $this->labelStorage;
        $ids && $labelsStorage->addFilterByIds('=', ...$ids);
        return $labelsStorage->getAll();
    }

    private function hasLocalFilter(): bool
    {
        return $this->localFilter;
    }

    private function standardizeData(array $data): array
    {
        return [
            'id' => $data['id'],
            'story' => [
                'id' => $data['story_id'],
                'title' => $data['story_title'],
                'summary' => $data['story_summary'],
                'body' => $data['story_body'],
                'datetime' => $data['story_datetime'],
                'uri' => $data['story_uri'],
                'views' => $data['story_views'],
                'status' => $data['story_status']

            ],
            'image' => [
                'id' => $data['cover_id'],
                'name' => $data['cover_name'],
                'description' => $data['cover_description'],
                'uri' => $data['cover_uri'],
                'extension' => $data['cover_extension'],
                'size' => $data['cover_size'],
                'views' => $data['cover_views'],
                'mimetype' => $data['cover_mimetype'],
                'date_hour' => $data['cover_date_hour'],
                'metadata' => $data['cover_metadata'],
                'status' => $data['cover_status']
            ],
            'labels' => $data['labels'] ?? new LabelsCollection,
            'status' => $data['status']
        ];
    }

    public function getAll(): Collection
    {
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->tableGet} as `news`
            {$this->generateSqlJoin()}
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            {$this->generateSqlLimit()}
        ");
        $this->bind($statement);
        if ($statement->execute() === false) {
            throw new Exception('ciebit.stories.storages.database.get_error', 2);
        }
        $collection = new Collection;
        $builder = new Builder;
        $newsData = $statement->fetchAll(PDO::FETCH_ASSOC);

        $labels = null;
        $labelsId = array_column($newsData, 'labels_id');
        $labelsId = array_filter(
            $labelsId,
            function($ids) { return $ids != null; }
        );
        $labelsId = array_map(
            function($ids){ return explode(',', $ids); },
            $labelsId
        );

        $labelsId[] = [];
        $labelsId = array_merge(...$labelsId);

        if (count($labelsId) > 0) {
            $labelsId = array_unique($labelsId);
            $labels = $this->getLabels($labelsId);
        }

        foreach ($newsData as $newsItemData) {
            if ($labels instanceof LabelsCollection && $newsItemData['labels_id'] != null) {
                $newsLabelsId = explode(',', $newsItemData['labels_id']);
                $newsItemData['labels'] = new LabelsCollection;
                foreach ($newsLabelsId as $id) {
                    $newsItemData['labels']->add($labels->getById((int) $id));
                }
            }
            $standarsizedData = $this->standardizeData($newsItemData);

            $story = $this->storyStorage->addFilterById($newsItemData['story_id'])->get();
            $News = new News($story, new Status($newsItemData['status']));
            $builder->setData($standarsizedData);
            $collection->add($News);
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

    public function getStoryStorage(): StoryStorage
    {
        return $this->storyStorage;
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

    public function setTableGet(string $name): Database
    {
        $this->tableGet = $name;
        return $this;
    }

    public function setTableLabelAssociation(string $name): Database
    {
        $this->tableLabelAssociation = $name;
        return $this;
    }

    public function setTableSave(string $name): Database
    {
        $this->tableSave = $name;
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
