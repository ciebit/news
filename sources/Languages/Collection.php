<?php
namespace Ciebit\News\Languages;

use ArrayIterator;
use ArrayObject;
use Ciebit\News\Languages\Reference;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class Collection implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var ArrayObject */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayObject;
    }

    public function add(Reference ...$references): self
    {
        foreach ($references as $reference) {
            $this->items->append($reference);
        }
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

    public function getByLanguageCode(string $languageCode): ?Reference
    {
        foreach ($this->getIterator() as $item) {
            if ($item->getLanguageCode() == $languageCode) {
                return $item;
            }
        }

        return null;
    }

    public function getById(string $id): ?Reference
    {
        foreach ($this->getIterator() as $item) {
            if ($item->getId() == $id) {
                return $item;
            }
        }

        return null;
    }

    public function getIterator(): ArrayIterator
    {
        return $this->items->getIterator();
    }

    public function jsonSerialize(): array
    {
        $json = [];

        foreach ($this->getIterator() as $item) {
            $json[$item->getLanguageCode()] = $item->getId();
        }

        return $json;
    }
}
