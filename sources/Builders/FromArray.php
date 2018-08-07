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
            ! is_array($this->data) OR
            ! isset($this->data['story_id'])
        ) {
            throw new Exception('ciebit.news.builders.invalid', 3);
        }
        $story = (new StoryBuilder)->setData([
            'title' => $this->data['story_title'],
            'status' => $this->data['story_status'],
            'body' => $this->data['story_body'],
            'date_hour' => $this->data['story_date_hour'],
            'id' => $this->data['story_id'],
            'summary' => $this->data['story_summary'],
            'uri' => $this->data['story_uri'],
            'views' => $this->data['story_views']
        ])->build();

        $image = (new ImageBuilder)->setData([
            'id' => $this->data['image_id'],
            'name' => $this->data['image_name'],
            'caption' => $this->data['image_caption'],
            'description' => $this->data['image_description'],
            'uri' => $this->data['image_uri'],
            'extension' => $this->data['image_extension'],
            'size' => $this->data['image_size'],
            'views' => $this->data['image_views'],
            'variations' => $this->data['image_variations'],
            'mimetype' => $this->data['image_mimetype'],
            'date_hour' => $this->data['image_date_hour'],
            'metadata' => $this->data['image_metadata'],
            'status' => $this->data['image_status']
        ])->build();

        $news = new News(
            $story,
            $image,
            new Status($this->data['status'])
        );
        return $news;
    }
}
