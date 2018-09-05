<?php
namespace Ciebit\News\Storages\Database;

use Ciebit\News\News;
use Ciebit\News\Collection;
use Ciebit\News\Storages\Storage;

interface Database extends Storage
{
    public function setTableGet(string $name): self;

    public function setTableLabelAssociation(string $name): self;

    public function setTableSave(string $name): self;
}
