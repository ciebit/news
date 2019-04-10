<?php
declare(strict_types=1);

namespace Ciebit\News\Tests;

use Ciebit\News\News;
use Ciebit\News\Status;
use PHPUnit\Framework\TestCase;

class NewsTest extends TestCase
{
    const COVER_ID = '5';
    const ID = '2';
    const LABELS_ID = ['3','4'];
    const STATUS = 3;
    const TITLE = 'New Title';

    public function testCreateFromManual(): void
    {
        $news = new News(self::TITLE, new Status(self::STATUS));
        $news->setId(self::ID)
        ->setCoverId(self::COVER_ID)
        ->setLabelsId(...self::LABELS_ID);

        $this->assertEquals(self::ID, $news->getId());
        $this->assertEquals(self::STATUS, $news->getStatus()->getValue());
        $this->assertEquals(self::TITLE, $news->getTitle());
        $this->assertEquals(self::COVER_ID, $news->getCoverId());
        $this->assertEquals(self::LABELS_ID, $news->getLabelsId());
    }
}
