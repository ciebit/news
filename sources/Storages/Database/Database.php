<?php
namespace Ciebit\News\Storages\Database;

use Ciebit\News\News;
use Ciebit\News\Collection;
use Ciebit\News\Storages\Storage;

interface Database extends Storage
{
    // public function delete(News $News): self;

    public function get(): ?News;

    public function getAll(): Collection;

    // public function save(News $News): self;
}
