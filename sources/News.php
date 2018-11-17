<?php
namespace Ciebit\News;

use Ciebit\Files\Images\Image;
use Ciebit\Labels\Collection as LabelsCollection;
use Ciebit\News\Status;
use Ciebit\Stories\Story;

class News extends Story
{
    private $image; #:?Image
    private $labels; #:LabelsCollection

    public function __construct(string $title, Status $status)
    {
        parent::__construct($title, $status);
        $this->labels = new LabelsCollection;
    }

    public function getCover(): ?Image
    {
        return $this->image;
    }

    public function getLabels(): LabelsCollection
    {
        return $this->labels;
    }

    public function setCover(Image $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function setLabels(LabelsCollection $labels): self
    {
        $this->labels = $labels;
        return $this;
    }
}
