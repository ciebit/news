<?php
namespace Ciebit\News;

use Ciebit\Files\Images\Image;
use Ciebit\News\Status;
use Ciebit\Stories\Story;

class News
{
    private $story; #:Story
    private $image; #:?Image
    private $status; #:Status

    public function __construct(Story $story, Image $image = null, Status $status)
    {
        $this->image = $image;
        $this->status = $status;
        $this->story = $story;
    }

    public function getCover(): ?Image
    {
        return $this->image;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getStory(): Story
    {
        return $this->story;
    }
}
