<?php
namespace Ciebit\News\Tests\Storages;

use Ciebit\Labels\Storages\Database\Sql as LabelsStorage;
use Ciebit\Stories\Storages\Database\Sql as StoryStorage;
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
        $labelStorage = new LabelsStorage($pdo);
        $storyStorage = new StoryStorage($pdo);
        return new DatabaseSql($pdo, $storyStorage, $labelStorage);
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
        $this->assertCount(5, $newsCollection->getIterator());
    }

    public function testGetAllBugUniqueValue(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByIds('=', 1, 2);
        $newsCollection = $database->getAll();
        $this->assertInstanceOf(Collection::class, $newsCollection);
    }

    public function testGetAllFilterById(): void
    {
        $id = 3;
        $database = $this->getDatabase();
        $database->addFilterById($id+0);
        $newsCollection = $database->getAll();
        $this->assertCount(1, $newsCollection->getIterator());
        $this->assertEquals($id, $newsCollection->getArrayObject()->offsetGet(0)->getId());
    }

    public function testGetAllFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus(Status::ACTIVE());
        $newsCollection = $database->getAll();
        $this->assertCount(1, $newsCollection->getIterator());
        $this->assertEquals(Status::ACTIVE(), $newsCollection->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testGetFilterByBody(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByBody('Text New 3');
        $news = $database->get();
        $this->assertEquals(3, $news->getId());

        $database = $this->getDatabase();
        $database->addFilterByBody('%New 2', 'LIKE');
        $news = $database->get();
        $this->assertEquals(2, $news->getId());

        $database = $this->getDatabase();
        $database->addFilterByBody('New five%', 'LIKE');
        $news = $database->get();
        $this->assertEquals(5, $news->getId());

        $database = $this->getDatabase();
        $database->addFilterByBody('%five%', 'LIKE');
        $news = $database->get();
        $this->assertEquals(5, $news->getId());
    }

    public function testGetFilterById(): void
    {
        $id = 2;
        $database = $this->getDatabase();
        $database->addFilterById($id+0);
        $news = $database->get();
        $this->assertEquals($id, $news->getId());
    }

    public function testGetFilterByIds(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByIds('=', 2, 3);
        $news = $database->getAll();
        $this->assertCount(2, $news);
        $this->assertEquals(2, $news->getById(2)->getId());
        $this->assertEquals(3, $news->getById(3)->getId());
    }

    public function testGetFilterByLabelId(): void
    {
        $id = 2;
        $database = $this->getDatabase();
        $database->addFilterByLabelId($id+0);
        $news = $database->get();
        $this->assertEquals(2, $news->getId());
        $this->assertEquals(2, $news->getLabels()->getArrayObject()->offsetGet(0)->getId());

        $database = $this->getDatabase();
        $database->addFilterByLabelId($id+0);
        $news = $database->getAll();
        $this->assertCount(1, $news);
        $this->assertEquals($id, $news->getArrayObject()->offsetGet(0)->getLabels()->getArrayObject()->offsetGet(0)->getId());
    }

    public function testGetFilterByLabelUri(): void
    {
        $uri = 'label-02';
        $database = $this->getDatabase();
        $database->addFilterByLabelUri($uri.'');
        $news = $database->get();
        $this->assertEquals(2, $news->getId());
        $this->assertEquals(2, $news->getLabels()->getArrayObject()->offsetGet(0)->getId());

        $database = $this->getDatabase();
        $database->addFilterByLabelUri($uri.'');
        $news = $database->getAll();
        $this->assertCount(1, $news);
        $this->assertEquals(2, $news->getArrayObject()->offsetGet(0)->getLabels()->getArrayObject()->offsetGet(0)->getId());
    }

    public function testGetFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus(Status::ACTIVE());
        $news = $database->get();
        $this->assertEquals(Status::ACTIVE(), $news->getStatus());
    }

    public function testGetFilterByTitle(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByTitle('Title New 3');
        $news = $database->get();
        $this->assertEquals(3, $news->getId());

        $database = $this->getDatabase();
        $database->addFilterByTitle('%New 2', 'LIKE');
        $news = $database->get();
        $this->assertEquals(2, $news->getId());

        $database = $this->getDatabase();
        $database->addFilterByTitle('New five%', 'LIKE');
        $news = $database->get();
        $this->assertEquals(5, $news->getId());

        $database = $this->getDatabase();
        $database->addFilterByTitle('%five%', 'LIKE');
        $news = $database->get();
        $this->assertEquals(5, $news->getId());
    }

    public function testGetFilterByUri(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByUri('title-new-3');
        $news = $database->get();
        $this->assertEquals(3, $news->getId());
    }

    public function testGetAllByOrderDesc(): void
    {
        $database = $this->getDatabase();
        $database->orderBy('id', 'DESC');
        $news = $database->get();
        $this->assertEquals(5, $news->getId());
    }

    public function testUpdate(): void
    {
        $id = 2;
        $views = 13;
        $database = $this->getDatabase();
        $database->addFilterById($id+0);
        $news = $database->get();
        $news->getStory()->setViews($views+0);
        $news = $database->update($news)->get();
        $this->assertEquals($views, $news->getStory()->getViews());
    }
}
