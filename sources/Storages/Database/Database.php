<?php
namespace Ciebit\News\Storages\Database;

use Ciebit\News\News;
use Ciebit\News\Collection;
use Ciebit\News\Storages\Storage;

interface Database extends Storage
{
    public function setTable(string $name): self;

    public function setTableLabelAssociation(string $name): self;
}
