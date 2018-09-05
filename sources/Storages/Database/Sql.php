<?php
declare(strict_types=1);
namespace Ciebit\News\Storages\Database;

use Ciebit\Labels\Storages\Storage as StorageLabel;
use Ciebit\News\Collection;
use Ciebit\News\Builders\FromArray as Builder;
use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\News\Storages\Storage;
use Exception;
use PDO;

class Sql extends SqlFilters implements Database
{
    private $pdo; #PDO
    private $tableGet; #string
    private $tableSave; #string

    public function __construct(PDO $pdo, StorageLabel $storageLabel)
    {
        $this->pdo = $pdo;
        $this->tableGet = 'cb_news_complete';
        $this->tableSave = 'cb_news';
    }

    public function addFilterByBody(string $body, string $operator = '='): Storage
    {
        $key = 'body';
        $sql = "`news`.`story_body` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_STR, $body);
        return $this;
    }

    public function addFilterById(int $id, string $operator = '='): Storage
    {
        $key = 'id';
        $sql = "`news`.`id` $operator :{$key}";
        $this->addfilter($key, $sql, PDO::PARAM_INT, $id);
        return $this;
    }

    public function addFilterByStatus(Status $status, string $operator = '='): Storage
    {
        $key = 'status';
        $sql = "`news`.`status` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_INT, $status->getValue());
        return $this;
    }

    public function addFilterByTitle(string $title, string $operator = '='): Storage
    {
        $key = 'title';
        $sql = "`news`.`story_title` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_STR, $title);
        return $this;
    }

    public function addFilterByUri(string $uri, string $operator = '='): Storage
    {
        $key = 'uri';
        $sql = "`news`.`story_uri` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_STR, $uri);
        return $this;
    }

    public function get(): ?News
    {
        $statement = $this->pdo->prepare("
            SELECT
            {$this->getFields()}
            FROM {$this->tableGet} as `news`
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            LIMIT 1
        ");
        $this->bind($statement);
        if ($statement->execute() === false) {
            throw new Exception('ciebit.stories.storages.database.get_error', 2);
        }
        $newsData = $statement->fetch(PDO::FETCH_ASSOC);
        if ($newsData == false) {
            return null;
        }

        $standarsizedData = $this->standardizeData($newsData);
        return (new Builder)->setData($standarsizedData)->build();
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
            'labels' => $data['labels'],
            'status' => $data['status']
        ];
    }

    public function getAll(): Collection
    {
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->tableGet} as `news`
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
        while ($news = $statement->fetch(PDO::FETCH_ASSOC)) {
            $standarsizedData = $this->standardizeData($news);
            $builder->setData($standarsizedData);
            $collection->add(
                $builder->build()
            );
        }
        return $collection;
    }

    private function getFields(): string
    {
        return '
            `news`.`id`,
            `news`.`story_id`,
            `news`.`story_title`,
            `news`.`story_summary`,
            `news`.`story_body`,
            `news`.`story_datetime`,
            `news`.`story_uri`,
            `news`.`story_views`,
            `news`.`story_status`,
            `news`.`cover_id`,
            `news`.`cover_name`,
            `news`.`cover_description`,
            `news`.`cover_uri`,
            `news`.`cover_extension`,
            `news`.`cover_size`,
            `news`.`cover_views`,
            `news`.`cover_mimetype`,
            `news`.`cover_date_hour`,
            `news`.`cover_metadata`,
            `news`.`cover_status`,
            `news`.`labels_id`,
            `news`.`status`
        ';
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
}
