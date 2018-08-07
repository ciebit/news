<?php
declare(strict_types=1);

namespace Ciebit\News\Builders\Strategies;

use Ciebit\News\News;
use Ciebit\News\Status;
use DateTime;

class FromArray implements Strategy
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
            ! is_array($this->data) OR
            ! isset($this->data['story_id'])
        ) {
            throw new Exception('ciebit.news.builders.invalid', 3);
        }
        $story = (new Story(
            $this->data['story_title'],
            $this->data['story_status']
        ));
        isset($this->data['story_body'])
        && $story->setBody($this->data['story_body']);
        isset($this->data['story_date_hour'])
        && $story->setDateTime($this->data['story_date_hour']);
        isset($this->data['story_id'])
        && $story->setId($this->data['story_id']);
        isset($this->data['story_summary'])
        && $story->setSummary($this->data['story_summary']);
        isset($this->data['story_uri'])
        && $story->setUri($this->data['story_uri']);
        isset($this->data['story_views'])
        && $story->setViews($this->data['story_views']);;

        $image = new Image;

        $news = new News(
            $story,
            $image,
            new Status($this->data['status'])
        );
        return $news;
    }
}
