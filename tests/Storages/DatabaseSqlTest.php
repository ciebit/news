<?php
namespace Ciebit\News\Tests\Storages;

use Ciebit\News\Collection;
use Ciebit\News\Status;
use Ciebit\News\News;
use Ciebit\News\Storages\Database\Sql as DatabaseSql;
use Ciebit\News\Tests\Connection;

class DatabaseSqlTest extends Connection
{
    public function testGet(): void
    {
        $this->database = new DatabaseSql($this->getPdo());
        $news = $this->database->get();
        $this->assertInstanceOf(News::class, $news);
    }

    public function testGetFilterByStatus(): void
    {
        $this->database = new DatabaseSql($this->getPdo());
        $this->database->addFilterByStatus(Status::ACTIVE());
        $news = $this->database->get();
        $this->assertEquals(Status::ACTIVE(), $news->getStatus());
    }

    public function testGetFilterById(): void
    {
        $id = 2;
        $this->database = new DatabaseSql($this->getPdo());
        $this->database->addFilterById($id+0);
        $news = $this->database->get();
        $this->assertEquals($id, $news->getId());
    }

    public function testGetAll(): void
    {
        $this->database = new DatabaseSql($this->getPdo());
        $newsCollection = $this->database->getAll();
        $this->assertInstanceOf(Collection::class, $newsCollection);
        $this->assertCount(4, $newsCollection->getIterator());
    }

    public function testGetAllFilterByStatus(): void
    {
        $this->database = new DatabaseSql($this->getPdo());
        $this->database->addFilterByStatus(Status::ACTIVE());
        $newsCollection = $this->database->getAll();
        $this->assertCount(1, $newsCollection->getIterator());
        $this->assertEquals(Status::ACTIVE(), $newsCollection->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testGetAllFilterById(): void
    {
        $id = 3;
        $this->database = new DatabaseSql($this->getPdo());
        $this->database->addFilterById($id+0);
        $newsCollection = $this->database->getAll();
        $this->assertCount(1, $newsCollection->getIterator());
        $this->assertEquals($id, $newsCollection->getArrayObject()->offsetGet(0)->getId());
    }
}
