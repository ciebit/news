<?php
declare(strict_types=1);
namespace Ciebit\News\Storages\Database;

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
    private $table; #string

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->table = 'cb_news_complete';
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

    public function get(): ?News
    {
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->table} as `news`
            WHERE {$this->generateSqlFilters()}
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
            'status' => $data['status']
        ];
    }

    public function getAll(): Collection
    {
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->table} as `news`
            WHERE {$this->generateSqlFilters()}
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
            `news`.`status`
        ';
    }

    public function getTotalRows(): int
    {
        return $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
    }

    public function setStartingLine(int $lineInit): Storage
    {
        parent::setOffset($lineInit);
        return $this;
    }

    public function setTable(string $name): self
    {
        $this->table = $name;
        return $this;
    }

    public function setTotalLines(int $total): Storage
    {
        parent::setLimit($total);
        return $this;
    }
}
