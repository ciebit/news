<?php
namespace Ciebit\News\Storages\Database;

use Ciebit\News\Collection;
use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\News\Storages\Storage;

interface Database extends Storage
{
    public function addFilterById(string $operator, string ...$ids): self;

    public function addFilterByLabelId(string $operator, int $id): self;

    public function addFilterByStatus(string $operator, Status $status): self;

    public function get(): ?News;

    public function getAll(): Collection;

    public function setStartingItem(int $lineInit): self;

    public function setTable(string $name): self;

    public function setTotalItems(int $total): self;

    public function setTableLabelAssociation(string $name): self;
}
