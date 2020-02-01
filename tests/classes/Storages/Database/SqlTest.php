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
use Ciebit\News\Factory\NewsFactory;
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
        $newsCollection = $database
        ->addFilterBySlug('LIKE', 'title-new%')
        ->setLimit(1)
        ->find();

        $this->assertCount(1, $newsCollection);
        $this->assertEquals(4, $database->getTotalItemsOfLastFindWithoutLimit());
    }

    public function testFind(): void
    {
        $database = $this->getDatabase();
        $news = $database->find()->getArrayObject()->offsetGet(0);
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
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals($id, $news->getAuthorId());
    }

    public function testFindFilterByBody(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByBody('=', 'Text new 3');
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals(3, $news->getId());
    }

    public function testFindFilterById(): void
    {
        $id = '2';
        $database = $this->getDatabase();
        $database->addFilterById('=', $id.'');
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals($id, $news->getId());
    }

    public function testFindFilterByIds(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', '2', '3');
        $news = $database->find();
        $this->assertCount(2, $news);
        $this->assertEquals('2', $news->getById('2')->getId());
        $this->assertEquals('3', $news->getById('3')->getId());
    }

    public function testFindFilterByLabelId(): void
    {
        $id = '2';
        $database = $this->getDatabase();
        $database->addFilterByLabelId('=', $id.'');
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals('2', $news->getId());
        $this->assertEquals('2', $news->getLabelsId()[0]);

        $database = $this->getDatabase();
        $database->addFilterByLabelId('=', $id.'');
        $news = $database->find();
        $this->assertCount(2, $news);
        $this->assertEquals($id, $news->getArrayObject()->offsetGet(0)->getLabelsId()[0]);
    }

    public function testFindFilterByLabelIdBugMultiples(): void
    {
        $ids = ['1', '2', '3'];
        $database = $this->getDatabase();
        $database->addFilterById('=', '1');
        $database->addFilterByLabelId('=', ...$ids);
        $newsCollection = $database->find();
        $this->assertCount(1, $newsCollection);
    }

    public function testFindFilterByLanguage(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByLanguage('=', 'en');
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals('2', $news->getId());
    }

    public function testFindFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals(Status::ACTIVE(), $news->getStatus());
    }

    public function testFindFilterByTitle(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByTitle('=', 'Title New 2');
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals('2', $news->getId());
    }

    public function testFindFilterBySlug(): void
    {
        $database = $this->getDatabase();
        $database->addFilterBySlug('=', 'title-new-3');
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals('3', $news->getId());
    }

    public function testFindCollection(): void
    {
        $database = $this->getDatabase();
        $newsCollection = $database->find();
        $this->assertInstanceOf(Collection::class, $newsCollection);
        $this->assertCount(6, $newsCollection->getIterator());
    }

    public function testFindAllBugUniqueValue(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', '1', '2');
        $newsCollection = $database->find();
        $this->assertInstanceOf(Collection::class, $newsCollection);
    }

    public function testFindAllFilterByBody(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByBody('LIKE', 'Text new%');
        $collection = $database->find();
        $this->assertCount(4, $collection);
    }

    public function testFindAllFilterById(): void
    {
        $id = '3';
        $database = $this->getDatabase();
        $database->addFilterById('=', $id.'');
        $newsCollection = $database->find();
        $this->assertCount(1, $newsCollection);
        $this->assertEquals($id, $newsCollection->getArrayObject()->offsetGet(0)->getId());
    }

    public function testFindAllFilterByLanguage(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByLanguage('=', 'en');
        $collection = $database->find();
        $this->assertCount(2, $collection);
    }

    public function testFindAllFilterByStatus(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByStatus('=', Status::ACTIVE());
        $newsCollection = $database->find();
        $this->assertCount(3, $newsCollection->getIterator());
        $this->assertEquals(Status::ACTIVE(), $newsCollection->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testFindAllFilterByTitle(): void
    {
        $database = $this->getDatabase();
        $database->addFilterByTitle('LIKE', 'Title New%');
        $newsCollection = $database->find();
        $this->assertCount(4, $newsCollection);
    }

    public function testFindAllFilterByUri(): void
    {
        $database = $this->getDatabase();
        $database->addFilterBySlug('LIKE', 'title-new-%');
        $newsCollection = $database->find();
        $this->assertCount(4, $newsCollection);
    }

    public function testFindAllByOrderDesc(): void
    {
        $database = $this->getDatabase();
        $database->addOrderBy(Storage::FIELD_DATETIME, 'DESC');
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals('4', $news->getId());
    }

    public function testDestroy(): void
    {
        $database = $this->getDatabase();
        $news = $database->addFilterById('=', '1')->find()->getArrayObject()->offsetGet(0);
        $database->destroy($news);
        $this->assertCount(0, $database->find());
    }

    public function testStore(): void
    {
        $factory = new NewsFactory;
        $news = $factory
            ->setTitle('News Store Title')
            ->setSummary('News Store Summary')
            ->setBody('News Store Body')
            ->setSlug('news-store-slug')
            ->setDateTime(new DateTime('2019-07-04 17:18:00'))
            ->setLanguage('en')
            ->setLanguageReferences(
                (new LanguageCollection)->add(
                    new LanguageReference('pt-BR', '2'),
                    new LanguageReference('es', '3')
                )
            )
            ->setStatus(Status::ACTIVE())
            ->setCoverId('77')
            ->setAuthorId('777')
            ->setViews(0)
            ->setLabelsId('1', '2', '3')
            ->create();

        $database = $this->getDatabase();
        $id = $database->store($news);

        $this->assertTrue('' !== $id);
        $news = $factory->setId($id)->create();
        $newsCopy = $database->addFilterById('=', $id)->find()->getArrayObject()->offsetGet(0);

        $this->assertEquals($news, $newsCopy);
    }

    public function testStoreNotLabels(): void
    {
        $factory = new NewsFactory;
        $news = $factory
            ->setTitle('News Store Title')
            ->setSummary('News Store Summary')
            ->setBody('News Store Body')
            ->setSlug('news-store-slug')
            ->setDateTime(new DateTime('2019-07-04 17:18:00'))
            ->setLanguage('en')
            ->setLanguageReferences(
                (new LanguageCollection)->add(
                    new LanguageReference('pt-BR', '2'),
                    new LanguageReference('es', '3')
                )
            )
            ->setStatus(Status::ACTIVE())
            ->setCoverId('77')
            ->setAuthorId('777')
            ->setViews(0)
            ->create();

        $database = $this->getDatabase();
        $id = $database->store($news);
        $newsCopy = $database->addFilterById('=', $id)->find()->getArrayObject()->offsetGet(0);
        $news = $factory->setId($id)->create();
        $this->assertEquals($news, $newsCopy);
    }

    public function testUpdate(): void
    {
        $database = $this->getDatabase();
        $database->addFilterById('=', '2');
        /** @var News */
        $news = $database->find()->getArrayObject()->offsetGet(0);
        $newsChanged = new News(
            $news->getTitle(),
            'Summary update',
            'Body update',
            'slug-update',
            new DateTime('2019-06-05 19:09:22'),
            'fr',
            (new LanguageCollection)->add(new LanguageReference('en', '4')),
            Status::ACTIVE(),
            '22',
            '2222',
            22,
            [],
            '2'
        );

        $newsUpdated = $database->update($newsChanged)->find()->getArrayObject()->offsetGet(0);
        $this->assertEquals($newsChanged, $newsUpdated);
    }
}
