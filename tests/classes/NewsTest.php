<?php

declare(strict_types=1);

namespace Ciebit\News\Tests;

use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\News\Languages\Collection as LanguageReferenceCollection;
use Ciebit\News\Languages\Reference as LanguageReference;
use DateTime;
use PHPUnit\Framework\TestCase;

class NewsTest extends TestCase
{
    const AUTHOR_ID = '6';
    const BODY = 'body news';
    const COVER_ID = '5';
    const DATE_TIME = '2020-01-21 15:57:00';
    const ID = '2';
    const LABELS_ID = ['3','4'];
    const LANGUAGE = 'en';
    const LANGUAGE_REFERENCE = ['pt-BR' => '1'];
    const SLUG = 'new-title';
    const STATUS = 3;
    const SUMMARY = 'Summary news';
    const TITLE = 'New Title';
    const VIEWS = 3;

    public function getNews(): News
    {
        $languageReferenceCollection = new LanguageReferenceCollection;

        foreach (self::LANGUAGE_REFERENCE as $code => $id) {
            $languageReferenceCollection->add(
                new LanguageReference($code, $id)
            );
        }

        $news = new News(
            self::TITLE, 
            self::SUMMARY,
            self::BODY,
            self::SLUG,
            new DateTime(self::DATE_TIME),
            self::LANGUAGE,
            $languageReferenceCollection,
            new Status(self::STATUS),
            self::COVER_ID,
            self::AUTHOR_ID,
            self::VIEWS,
            self::LABELS_ID,
            self::ID
        );

        return $news;
    }

    public function testCreateFromManual(): void
    {
        $news = $this->getNews();

        $this->assertEquals(self::ID, $news->getId());
        $this->assertEquals(self::STATUS, $news->getStatus()->getValue());
        $this->assertEquals(self::TITLE, $news->getTitle());
        $this->assertEquals(self::AUTHOR_ID, $news->getAuthorId());
        $this->assertEquals(self::COVER_ID, $news->getCoverId());
        $this->assertEquals(self::LABELS_ID, $news->getLabelsId());
    }

    public function testJsonSerialize(): void
    {
        $news = self::getNews();
        $json = json_encode($news);
        $this->assertJson($json);

        $data = json_decode($json);
        $this->assertEquals($news->getDateTime()->format('Y-m-d H:i:s'), $data->dateTime);
        $this->assertEquals($news->getId(), $data->id);
        $this->assertEquals($news->getSlug(), $data->slug);
        $this->assertEquals($news->getStatus()->getValue(), $data->status);
    }
}
