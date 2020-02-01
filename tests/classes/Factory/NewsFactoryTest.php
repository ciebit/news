<?php

declare(strict_types=1);

namespace Ciebit\News\Tests\Factory;

use Ciebit\News\Factory\NewsFactory;
use Ciebit\News\Status;
use Ciebit\News\Languages\Collection as LanguageCollection;
use Ciebit\News\Languages\Reference as LanguageReference;
use DateTime;
use PHPUnit\Framework\TestCase;

class NewsFactoryTest extends TestCase
{
    public const AUTHOR_ID = '6';
    public const BODY = 'body news';
    public const COVER_ID = '5';
    public const DATE_TIME = '2020-01-21 15:57:00';
    public const ID = '2';
    public const LABELS_ID = ['3', '4'];
    public const LANGUAGE = 'en';
    public const SLUG = 'new-title';
    public const STATUS = 3;
    public const SUMMARY = 'Summary news';
    public const TITLE = 'New Title';
    public const VIEWS = 3;

    public function testCreate(): void
    {
        $languageCollection = (new LanguageCollection)->add(
            new LanguageReference('pt-BR', '2'),
            new LanguageReference('es', '3'),
        );
        $factory = new NewsFactory;
        $news = $factory
            ->setAuthorId(self::AUTHOR_ID)
            ->setBody(self::BODY)
            ->setCoverId(self::COVER_ID)
            ->setDateTime(new DateTime(self::DATE_TIME))
            ->setId(self::ID)
            ->setLabelsId(...self::LABELS_ID)
            ->setLanguage(self::LANGUAGE)
            ->setLanguageReferences($languageCollection)
            ->setSlug(self::SLUG)
            ->setSummary(self::SUMMARY)
            ->setStatus(new Status(self::STATUS))
            ->setTitle(self::TITLE)
            ->setViews(self::VIEWS)
            ->create();

        $this->assertEquals(self::AUTHOR_ID, $news->getAuthorId());
        $this->assertEquals(self::BODY, $news->getBody());
        $this->assertEquals(self::COVER_ID, $news->getCoverId());
        $this->assertEquals(self::DATE_TIME, $news->getDateTime()->format('Y-m-d H:i:s'));
        $this->assertEquals(self::LABELS_ID, $news->getLabelsId());
        $this->assertEquals(self::LANGUAGE, $news->getLanguage());
        $this->assertEquals($languageCollection, $news->getLanguageReferences());
        $this->assertEquals(self::SLUG, $news->getSlug());
        $this->assertEquals(self::SUMMARY, $news->getSummary());
        $this->assertEquals(self::STATUS, $news->getStatus()->getValue());
        $this->assertEquals(self::TITLE, $news->getTitle());
        $this->assertEquals(self::VIEWS, $news->getViews());
        $this->assertEquals(self::ID, $news->getId());
    }
}
