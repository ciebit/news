<?php
namespace Ciebit\News\Storages;

use Ciebit\News\Collection;
use Ciebit\News\News;
use Ciebit\News\Status;

interface Storage
{
    public function addFilterById(string $operator, int ...$id): self;

    public function addFilterByLabelId(string $operator, int $id): self;

    public function addFilterByStatus(string $operator, Status $status): self;

    public function get(): ?News;

    public function getAll(): Collection;

    public function setStartingLine(int $lineInit): self;

    public function setTotalLines(int $total): self;

    /**
     * @throw Exception
    */
    public function update(News $news): self;
}
