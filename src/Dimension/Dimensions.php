<?php
namespace Trendwerk\Search\Dimension;

final class Dimensions
{
    private $dimensions = [];

    public function add(Dimension $dimension) {
        $this->dimensions[] = $dimension;
    }
}
