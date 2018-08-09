<?php
namespace Ciebit\News;

use Ciebit\Files\Images\Image;
use Ciebit\News\Status;
use Ciebit\Stories\Story;

class News
{
    private $id; #:int
    private $story; #:Story
    private $image; #:?Image
    private $status; #:Status

    public function __construct(Story $story, Status $status)
    {
        $this->id = 0;
        $this->status = $status;
        $this->story = $story;
    }

    public function getId(): int
    {
        return $this->id;
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

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setImage(Image $image): self
    {
        $this->image = $image;
        return $this;
    }
}
