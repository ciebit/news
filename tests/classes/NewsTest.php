<?php
namespace Ciebit\News\Tests;

use Ciebit\Labels\Collection as LabelsCollection;
use Ciebit\Labels\Label;
use Ciebit\Labels\Status as LabelStatus;
use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\Stories\Story;
use Ciebit\Files\Images\Image;
use Ciebit\Files\Status as FileStatus;
use Ciebit\Stories\Status as StoryStatus;
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
    const STATUS = 1;
    const STORY_TITLE = 'Story Title';
    const STORY_STATUS = 3;

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

        $story = new Story(
            self::STORY_TITLE,
            new StoryStatus(self::STORY_STATUS)
        );

        $labels = new LabelsCollection;
        $labels->add(new Label(
            self::LABEL_TITLE,
            self::LABEL_URI,
            new LabelStatus(self::LABEL_STATUS)
        ));

        $news = new News($story, new Status(self::STATUS));
        $news->setId(self::ID)
        ->setImage($image)
        ->setLabels($labels);

        $this->assertEquals(self::ID, $news->getId());
        $this->assertEquals(self::STATUS, $news->getStatus()->getValue());
        $this->assertInstanceof(Story::class, $news->getStory());
        $this->assertEquals(self::STORY_TITLE, $news->getStory()->getTitle());
        $this->assertEquals(self::STORY_STATUS, $news->getStory()->getStatus()->getValue());
        $this->assertInstanceof(Image::class, $news->getCover());
        $this->assertEquals(self::IMAGE_NAME, $news->getCover()->getName());
        $this->assertEquals(self::IMAGE_MIMETYPE, $news->getCover()->getMimetype());
        $this->assertEquals(self::IMAGE_URI, $news->getCover()->getUri());
        $this->assertEquals(self::IMAGE_WIDTH, $news->getCover()->getWidth());
        $this->assertEquals(self::IMAGE_HEIGHT, $news->getCover()->getHeight());
        $this->assertEquals(self::IMAGE_STATUS, $news->getCover()->getStatus()->getValue());

        $newLabels = $news->getLabels();
        $this->assertInstanceof(LabelsCollection::class, $newLabels);
        $label = $newLabels->getArrayObject()->offsetGet(0);
        $this->assertEquals(self::LABEL_TITLE, $label->getTitle());
        $this->assertEquals(self::LABEL_URI, $label->getUri());
        $this->assertEquals(self::LABEL_STATUS, $label->getStatus()->getValue());
    }
}
