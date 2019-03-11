<?php
namespace Ciebit\News\Tests\Storages\Database;

use Ciebit\Files\Storages\Database\Sql as FilesStorage;
use Ciebit\Labels\Storages\Database\Sql as LabelsStorage;
use Ciebit\News\Collection;
use Ciebit\News\LanguageReference;
use Ciebit\News\Status;
use Ciebit\News\News;
use Ciebit\News\Storages\Database\Sql as DatabaseSql;
use Ciebit\News\Tests\Connection;
use ArrayObject;

class SqlTest extends Connection
{
    public function getDatabase(): DatabaseSql
    {
        $pdo = $this->getPdo();
        $labelStorage = new LabelsStorage($pdo);
        $filesStorage = new FilesStorage($pdo, $labelStorage);
        return new DatabaseSql($pdo, $filesStorage, $labelStorage);
    }

    public function testGet(): void
    {
        $database = $this->getDatabase();
        $news = $database->get();
        $this->assertInstanceOf(News::class, $news);
        $this->assertEquals(1, $news->getId());
        $this->assertEquals(1, $news->getCover()->getId());
        $this->assertEquals('Title New 1', $news->getTitle());
        $this->assertEquals('Summary new 1', $news->getSummary());
        $this->assertEquals('Text new 1', $news->getBody());
        $this->assertEquals('2018-07-29 16:26:00', $news->getDateTime()->format('Y-m-d H:i:s'));
        $this->assertEquals('title-new-1', $news->getUri());
        $this->assertEquals(11, $news->getViews());
        $this->assertEquals('pt-BR', $news->getLanguage());
        $this->assertInstanceOf(ArrayObject::class, $news->getLanguageReferences());

        $languageReferences = $news->getLanguageReferences()->offsetGet(0);
        $this->assertInstanceOf(LanguageReference::class, $languageReferences);
        $this->assertEquals(2, $languageReferences->getReferenceId());
        $this->assertEquals('en', $languageReferences->getLanguageCode());
        $this->assertEquals(1, $news->getStatus()->getValue());
    }

    public function testGetFilterByBody(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByBody('=', 'Text new 3');
        $news = $database->get();
        $this->assertEquals(3, $news->getId());
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

    public function testGetFilterByLanguage(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByLanguage('=', 'en');
        $news = $database->get();
        $this->assertEquals(2, $news->getId());
    }

    public function testGetFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $news = $database->get();
        $this->assertEquals(Status::ACTIVE(), $news->getStatus());
    }

    public function testGetFilterByTitle(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByTitle('=', 'Title New 2');
        $news = $database->get();
        $this->assertEquals(2, $news->getId());
    }

    public function testGetFilterByUri(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByUri('=', 'title-new-3');
        $news = $database->get();
        $this->assertEquals(3, $news->getId());
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

    public function testGetAllFilterByBody(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByBody('LIKE', 'Text new%');
        $collection = $database->getAll();
        $this->assertCount(4, $collection);
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

    public function testGetAllFilterByLanguage(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByLanguage('=', 'en');
        $collection = $database->getAll();
        $this->assertCount(2, $collection);
    }

    public function testGetAllFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $newsCollection = $database->getAll();
        $this->assertCount(3, $newsCollection->getIterator());
        $this->assertEquals(Status::ACTIVE(), $newsCollection->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testGetAllFilterByTitle(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByTitle('LIKE', 'Title New%');
        $newsCollection = $database->getAll();
        $this->assertCount(4, $newsCollection);
    }

    public function testGetAllFilterByUri(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByUri('LIKE', 'title-new-%');
        $newsCollection = $database->getAll();
        $this->assertCount(4, $newsCollection);
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
