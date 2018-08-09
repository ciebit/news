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

    public function setData(array $data): Builder
    {
        $this->data = $data;
        return $this;
    }

    public function build(): News
    {
        if (
            ! is_array($this->data) OR
            ! $this->data['story'] OR
            ! $this->data['image'] OR
            ! $this->data['id']
        ) {
            throw new Exception('ciebit.news.builders.invalid', 3);
        }
        $story = (new StoryBuilder)->setData($this->data['story'])->build();
        $image = (new ImageBuilder)->setData($this->data['image'])->build();
        $status = $this->data['status'] ? new Status((int) $this->data['status']) : Status::DRAFT();

        $news = (new News(
            $story,
            $image,
            $status
        ))
        ->setId((int) $this->data['id']);

        return $news;
    }
}
