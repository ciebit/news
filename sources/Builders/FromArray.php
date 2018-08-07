<?php
declare(strict_types=1);

namespace Ciebit\News\Builders;

use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\Stories\Builders\FromArray as StoryBuilder;
use Ciebit\Files\Images\Builders\FromArray as ImageBuilder;
use DateTime;

class FromArray implements Builder
{
    private $data; #:array

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function build(): News
    {
        if (
            ! is_array($this->data)
        ) {
            throw new Exception('ciebit.news.builders.invalid', 3);
        }
        $story = (new StoryBuilder)->setData($this->data['story'])->build();
        $image = (new ImageBuilder)->setData($this->data['image'])->build();

        $news = new News(
            $story,
            $image,
            new Status($this->data['status'])
        );
        return $news;
    }
}
