<?php
namespace Ciebit\News\Tests;

use Ciebit\Files\Images\Image;
use Ciebit\Files\Status as FileStatus;
use Ciebit\Labels\Collection as LabelsCollection;
use Ciebit\Labels\Label;
use Ciebit\Labels\Status as LabelStatus;
use Ciebit\News\News;
use Ciebit\News\Status;
use PHPUnit\Framework\TestCase;

class NewsTest extends TestCase
{
    const ID = 2;
    const IMAGE_HEIGHT = 300;
    const IMAGE_MIMETYPE = 'image/jpeg';
    const IMAGE_NAME = 'Image Name';
    const IMAGE_STATUS = 4;
    const IMAGE_URI = 'image-name.jpeg';
    const IMAGE_WIDTH = 400;
    const LABEL_TITLE = 'Label Title';
    const LABEL_URI = 'label-title';
    const LABEL_STATUS = 2;
    const STATUS = 3;
    const TITLE = 'New Title';

    public function testCreateFromManual(): void
    {
        $image = new Image(
            self::IMAGE_NAME,
            self::IMAGE_MIMETYPE,
            self::IMAGE_URI,
            self::IMAGE_WIDTH,
            self::IMAGE_HEIGHT,
            new FileStatus(self::IMAGE_STATUS)
        );

        $labels = new LabelsCollection;
        $labels->add(new Label(
            self::LABEL_TITLE,
            self::LABEL_URI,
            new LabelStatus(self::LABEL_STATUS)
        ));

        $news = new News(self::TITLE, new Status(self::STATUS));
        $news->setId(self::ID)
        ->setCover($image)
        ->setLabels($labels);

        $this->assertEquals(self::ID, $news->getId());
        $this->assertEquals(self::STATUS, $news->getStatus()->getValue());
        $this->assertEquals(self::TITLE, $news->getTitle());
        $this->assertEquals(self::STATUS, $news->getStatus()->getValue());
        $this->assertInstanceof(Image::class, $news->getCover());

        $newLabels = $news->getLabels();
        $this->assertInstanceof(LabelsCollection::class, $newLabels);
        $label = $newLabels->getArrayObject()->offsetGet(0);
        $this->assertEquals(self::LABEL_TITLE, $label->getTitle());
        $this->assertEquals(self::LABEL_URI, $label->getSlug());
        $this->assertEquals(self::LABEL_STATUS, $label->getStatus()->getValue());
    }
}
