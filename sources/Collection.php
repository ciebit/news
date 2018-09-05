<?php
namespace Ciebit\News;

use ArrayIterator;
use ArrayObject;
use Countable;
use IteratorAggregate;

class Collection implements Countable, IteratorAggregate
{
    private $newsList; #:ArrayObject

    public function __construct()
    {
        $this->newsList = new ArrayObject;
    }

    public function add(News $news): self
    {
        $this->newsList->append($news);
        return $this;
    }

    public function getArrayObject(): ArrayObject
    {
        return clone $this->newsList;
    }

    public function getById(int $id): ?News
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
        return $this->newsList->getIterator();
    }

    public function count(): int
    {
        return $this->newsList->count();
    }
}
