<?php
namespace Ciebit\News\Tests\Storages;

use Ciebit\Files\Storages\Database\Sql as FilesStorage;
use Ciebit\Labels\Storages\Database\Sql as LabelsStorage;
use Ciebit\News\Collection;
use Ciebit\News\Status;
use Ciebit\News\News;
use Ciebit\News\Storages\Database\Sql as DatabaseSql;
use Ciebit\News\Tests\Connection;

class DatabaseSqlTest extends Connection
{
    public function getDatabase(): DatabaseSql
    {
        $pdo = $this->getPdo();
        $filesStorage = new FilesStorage($pdo);
        $labelStorage = new LabelsStorage($pdo);
        return new DatabaseSql($pdo, $filesStorage, $labelStorage);
    }

    public function testGet(): void
    {
        $database = $this->getDatabase();
        $news = $database->get();
        $this->assertInstanceOf(News::class, $news);
    }

    public function testGetAll(): void
    {
        $database = $this->getDatabase();
        $newsCollection = $database->getAll();
        $this->assertInstanceOf(Collection::class, $newsCollection);
        $this->assertCount(6, $newsCollection->getIterator());
    }

    public function testGetAllBugUniqueValue(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', 1, 2);
        $newsCollection = $database->getAll();
        $this->assertInstanceOf(Collection::class, $newsCollection);
    }

    public function testGetAllFilterById(): void
    {
        $id = 3;
        $database = $this->getDatabase();
        $database->addFilterById('=', $id+0);
        $newsCollection = $database->getAll();
        $this->assertCount(1, $newsCollection);
        $this->assertEquals($id, $newsCollection->getArrayObject()->offsetGet(0)->getId());
    }

    public function testGetAllFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $newsCollection = $database->getAll();
        $this->assertCount(3, $newsCollection->getIterator());
        $this->assertEquals(Status::ACTIVE(), $newsCollection->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testGetFilterById(): void
    {
        $id = 2;
        $database = $this->getDatabase();
        $database->addFilterById('=', $id+0);
        $news = $database->get();
        $this->assertEquals($id, $news->getId());
    }

    public function testGetFilterByIds(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', 2, 3);
        $news = $database->getAll();
        $this->assertCount(2, $news);
        $this->assertEquals(2, $news->getById(2)->getId());
        $this->assertEquals(3, $news->getById(3)->getId());
    }

    public function testGetFilterByLabelId(): void
    {
        $id = 2;
        $database = $this->getDatabase();
        $database->addFilterByLabelId('=', $id+0);
        $news = $database->get();
        $this->assertEquals(2, $news->getId());
        $this->assertEquals(2, $news->getLabels()->getArrayObject()->offsetGet(0)->getId());

        $database = $this->getDatabase();
        $database->addFilterByLabelId('=', $id+0);
        $news = $database->getAll();
        $this->assertCount(2, $news);
        $this->assertEquals($id, $news->getArrayObject()->offsetGet(0)->getLabels()->getArrayObject()->offsetGet(0)->getId());
    }

    public function testGetFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $news = $database->get();
        $this->assertEquals(Status::ACTIVE(), $news->getStatus());
    }

    public function testGetAllByOrderDesc(): void
    {
        $database = $this->getDatabase();
        $database->orderBy('datetime', 'DESC');
        $news = $database->get();
        $this->assertEquals('4', $news->getId());
    }

    // Disabled
    public function Update(): void
    {
        $id = 2;
        $views = 13;
        $database = $this->getDatabase();
        $database->addFilterById('=', $id+0);
        $news = $database->get();
        $news->setViews($views+0);
        $news = $database->update($news)->get();
        $this->assertEquals($views, $news->getStory()->getViews());
    }
}
