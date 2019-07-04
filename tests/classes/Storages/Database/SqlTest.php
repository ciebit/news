<?php
declare(strict_types=1);

namespace Ciebit\News\Tests\Storages\Database;

use Ciebit\News\Collection;
use Ciebit\News\LanguageReference;
use Ciebit\News\Status;
use Ciebit\News\News;
use Ciebit\News\Storages\Database\Sql as DatabaseSql;
use Ciebit\News\Storages\Storage;
use Ciebit\News\Tests\Connection;
use ArrayObject;

class SqlTest extends Connection
{
    public function getDatabase(): DatabaseSql
    {
        $pdo = $this->getPdo();
        return new DatabaseSql($pdo);
    }

    public function testGetTotalItemsOfLastFindWithoutLimit(): void
    {
        $database = $this->getDatabase();
        $news = $database
        ->addFilterBySlug('LIKE', 'title-new%')
        ->setLimit(1)
        ->findAll();

        $this->assertCount(1, $news);
        $this->assertEquals(4, $database->getTotalItemsOfLastFindWithoutLimit());
    }

    public function testFind(): void
    {
        $database = $this->getDatabase();
        $news = $database->findOne();
        $this->assertInstanceOf(News::class, $news);
        $this->assertEquals('1', $news->getId());
        $this->assertEquals('1', $news->getCoverId());
        $this->assertEquals('111', $news->getAuthorId());
        $this->assertEquals('Title New 1', $news->getTitle());
        $this->assertEquals('Summary new 1', $news->getSummary());
        $this->assertEquals('Text new 1', $news->getBody());
        $this->assertEquals('2018-07-29 16:26:00', $news->getDateTime()->format('Y-m-d H:i:s'));
        $this->assertEquals('title-new-1', $news->getSlug());
        $this->assertEquals(11, $news->getViews());
        $this->assertEquals('pt-BR', $news->getLanguage());

        $languageReferences = $news->getLanguageReferences();
        $this->assertTrue(is_array($languageReferences));
        $this->assertInstanceOf(LanguageReference::class, $languageReferences[0]);
        $this->assertEquals(2, $languageReferences[0]->getReferenceId());
        $this->assertEquals('en', $languageReferences[0]->getLanguageCode());
        $this->assertEquals(1, $news->getStatus()->getValue());
    }

    public function testFindFilterByAuthorId(): void
    {
        $id = '222';
        $database = $this->getDatabase();
        $database->addFilterByAuthorId('=', $id.'');
        $news = $database->findOne();
        $this->assertEquals($id, $news->getAuthorId());
    }

    public function testFindFilterByBody(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByBody('=', 'Text new 3');
        $news = $database->findOne();
        $this->assertEquals(3, $news->getId());
    }

    public function testFindFilterById(): void
    {
        $id = '2';
        $database = $this->getDatabase();
        $database->addFilterById('=', $id.'');
        $news = $database->findOne();
        $this->assertEquals($id, $news->getId());
    }

    public function testFindFilterByIds(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', '2', '3');
        $news = $database->findAll();
        $this->assertCount(2, $news);
        $this->assertEquals('2', $news->getById('2')->getId());
        $this->assertEquals('3', $news->getById('3')->getId());
    }

    public function testFindFilterByLabelId(): void
    {
        $id = '2';
        $database = $this->getDatabase();
        $database->addFilterByLabelId('=', $id.'');
        $news = $database->findOne();
        $this->assertEquals('2', $news->getId());
        $this->assertEquals('2', $news->getLabelsId()[0]);

        $database = $this->getDatabase();
        $database->addFilterByLabelId('=', $id.'');
        $news = $database->findAll();
        $this->assertCount(2, $news);
        $this->assertEquals($id, $news->getArrayObject()->offsetGet(0)->getLabelsId()[0]);
    }

    public function testFindFilterByLanguage(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByLanguage('=', 'en');
        $news = $database->findOne();
        $this->assertEquals('2', $news->getId());
    }

    public function testFindFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $news = $database->findOne();
        $this->assertEquals(Status::ACTIVE(), $news->getStatus());
    }

    public function testFindFilterByTitle(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByTitle('=', 'Title New 2');
        $news = $database->findOne();
        $this->assertEquals('2', $news->getId());
    }

    public function testFindFilterBySlug(): void
    {
        $database = $this->getDatabase();
        $database->addFilterBySlug('=', 'title-new-3');
        $news = $database->findOne();
        $this->assertEquals('3', $news->getId());
    }

    public function testFindAll(): void
    {
        $database = $this->getDatabase();
        $newsCollection = $database->findAll();
        $this->assertInstanceOf(Collection::class, $newsCollection);
        $this->assertCount(6, $newsCollection->getIterator());
    }

    public function testFindAllBugUniqueValue(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', '1', '2');
        $newsCollection = $database->findAll();
        $this->assertInstanceOf(Collection::class, $newsCollection);
    }

    public function testFindAllFilterByBody(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByBody('LIKE', 'Text new%');
        $collection = $database->findAll();
        $this->assertCount(4, $collection);
    }

    public function testFindAllFilterById(): void
    {
        $id = '3';
        $database = $this->getDatabase();
        $database->addFilterById('=', $id.'');
        $newsCollection = $database->findAll();
        $this->assertCount(1, $newsCollection);
        $this->assertEquals($id, $newsCollection->getArrayObject()->offsetGet(0)->getId());
    }

    public function testFindAllFilterByLanguage(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByLanguage('=', 'en');
        $collection = $database->findAll();
        $this->assertCount(2, $collection);
    }

    public function testFindAllFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $newsCollection = $database->findAll();
        $this->assertCount(3, $newsCollection->getIterator());
        $this->assertEquals(Status::ACTIVE(), $newsCollection->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testFindAllFilterByTitle(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByTitle('LIKE', 'Title New%');
        $newsCollection = $database->findAll();
        $this->assertCount(4, $newsCollection);
    }

    public function testFindAllFilterByUri(): void
    {
        $database = $this->getDatabase();
        $database->addFilterBySlug('LIKE', 'title-new-%');
        $newsCollection = $database->findAll();
        $this->assertCount(4, $newsCollection);
    }

    public function testFindAllByOrderDesc(): void
    {
        $database = $this->getDatabase();
        $database->addOrderBy(Storage::FIELD_DATETIME, 'DESC');
        $news = $database->findOne();
        $this->assertEquals('4', $news->getId());
    }

    // Disabled
    public function Update(): void
    {
        $id = '2';
        $views = 13;
        $database = $this->getDatabase();
        $database->addFilterById('=', $id.'');
        $news = $database->findOne();
        $news->setViews($views+0);
        $news = $database->update($news)->get();
        $this->assertEquals($views, $news->getStory()->getViews());
    }
}
