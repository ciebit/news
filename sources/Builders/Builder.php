<?php
namespace Ciebit\News\Builders;

use Ciebit\News\News;

interface Builder
{
    public function setData(array $data): self;

    public function build(): News;
}
