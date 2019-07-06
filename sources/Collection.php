<?php
namespace Ciebit\News;

use ArrayIterator;
use ArrayObject;
use Ciebit\News\News;
use Countable;
use IteratorAggregate;

class Collection implements Countable, IteratorAggregate
{
    /** @var ArrayObject */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayObject;
    }

    public function add(News $news): self
    {
        $this->items->append($news);
        return $this;
    }

    public function count(): int
    {
        return $this->items->count();
    }

    public function getArrayObject(): ArrayObject
    {
        return clone $this->items;
    }

    public function getById(string $id): ?News
    {
        foreach ($this->getIterator() as $news) {
            if ($news->getId() == $id) {
                return $news;
            }
        }

        return null;
    }

    public function getIterator(): ArrayIterator
    {
        return $this->items->getIterator();
    }
}
