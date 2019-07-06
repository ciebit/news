<?php
declare(strict_types=1);

namespace Ciebit\News\Tests\Storages\Database;

use Ciebit\News\Collection;
use Ciebit\News\Languages\Collection as LanguageCollection;
use Ciebit\News\Languages\Reference as LanguageReference;
use Ciebit\News\Status;
use Ciebit\News\News;
use Ciebit\News\Storages\Database\Sql as DatabaseSql;
use Ciebit\News\Storages\Storage;
use Ciebit\News\Tests\Connection;
use ArrayObject;
use DateTime;

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

        $languageCollection = $news->getLanguageReferences();
        $languageReference = $languageCollection->getArrayObject()->offsetGet(0);
        $this->assertInstanceOf(LanguageCollection::class, $languageCollection);
        $this->assertInstanceOf(LanguageReference::class, $languageReference);
        $this->assertEquals(2, $languageReference->getId());
        $this->assertEquals('en', $languageReference->getLanguageCode());
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

    public function testDestroy(): void
    {
        $database = $this->getDatabase();
        $news = $database->addFilterById('=', '1')->findOne();
        $database->destroy($news);
        $this->assertNull($database->findOne());
    }

    public function testStore(): void
    {
        $news = new News('News Store Title', Status::ACTIVE());
        $news->setAuthorId('777')
        ->setBody('News Store Body')
        ->setCoverId('77')
        ->setDateTime(new DateTime('2019-07-04 17:18:00'))
        ->setLanguage('en')
        ->setLabelsId('1', '2', '3')
        ->addLanguageReference(
            new LanguageReference('pt-BR', '2'),
            new LanguageReference('es', '3')
        )
        ->setSummary('News Store Summary')
        ->setSlug('news-store-slug')
        ;

        $database = $this->getDatabase();
        $database->store($news);
        $this->assertTrue($news->getId() > 0);

        $newsCopy = $database->addFilterById('=', $news->getId())->findOne();

        $this->assertEquals($news, $newsCopy);
    }

    public function testStoreNotLabels(): void
    {
        $news = new News('News Store Title 2', Status::ACTIVE());
        $database = $this->getDatabase();
        $database->store($news);
        $newsCopy = $database->addFilterById('=', $news->getId())->findOne();
        $this->assertEquals($news, $newsCopy);
    }

    public function testUpdate(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', '2');
        $news = $database->findOne();
        $news
        ->setCoverId('22')
        ->setAuthorId('2222')
        ->setSummary('Summary update')
        ->setBody('Body update')
        ->setDateTime(new DateTime('2019-06-05 19:09:22'))
        ->setSlug('slug-update')
        ->setLanguage('fr')
        ->addLanguageReference(new LanguageReference('en', '4'))
        ->setViews(22)
        ->setStatus(Status::ACTIVE());

        $newsUpdated = $database->update($news)->findOne();
        $this->assertEquals($news, $newsUpdated);
    }
}
