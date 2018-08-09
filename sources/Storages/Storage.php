<?php
namespace Ciebit\News\Storages;

use Ciebit\News\Collection;
use Ciebit\News\News;
use Ciebit\News\Status;

interface Storage
{
    public function addFilterById(int $id, string $operator = '='): self;
    public function addFilterByStatus(Status $status, string $operator = '='): self;
    public function get(): ?News;
    public function getAll(): Collection;
    public function setStartingLine(int $lineInit): self;
    public function setTotalLines(int $total): self;
}
