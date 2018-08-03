<?php
namespace Ciebit\News\Builders;

use Ciebit\News\Builders\Strategies\Strategy;
use Ciebit\News\News;

class Builder
{
    private $strategy;

    public function __construct(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function build(): News
    {
        return $this->strategy->build();
    }
}
