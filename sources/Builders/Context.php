<?php
namespace Ciebit\News\Builders;

use Ciebit\News\News;
use Ciebit\News\Builders\Strategies\FromArray;

class Context
{
    private $data; #any

    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    public function build(): News
    {
        if (is_array($this->data)) {
            $strategy = (new FromArray)->setData($this->data);
        }
        return (new Builder($strategy))->build();
    }
}
