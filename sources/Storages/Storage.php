<?php
namespace Ciebit\Files\Storages;

use Ciebit\Files\Collection;
use Ciebit\Files\File;
use Ciebit\Files\Status;

interface Storage
{
    public function addFilterById(int $id, string $operator = '='): self;
    public function addFilterByStatus(Status $status, string $operator = '='): self;
    public function get(): ?File;
    public function getAll(): Collection;
    public function setStartingLine(int $lineInit): self;
    public function setTotalLines(int $total): self;
}
