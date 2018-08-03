<?php
declare(strict_types=1);

namespace Ciebit\News\Builders\Strategies;

use Ciebit\News\News;

interface Strategy
{
    public function build(): News;
}
