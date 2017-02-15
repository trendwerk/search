<?php
namespace Trendwerk\Search;

use Trendwerk\Search\Dimension\Dimension;
use Trendwerk\Search\Dimension\Dimensions;
use Trendwerk\Search\Hook\Posts;
use wpdb;

final class Search
{
    private $dimensions;

    public function __construct(wpdb $wpdb)
    {
        $this->dimensions = new Dimensions();
        $this->postsHook = new Posts($wpdb, $this->dimensions);
    }

    public function init()
    {
        $this->postsHook->init();
    }

    public function addDimension(Dimension $dimension)
    {
        $this->dimensions->add($dimension);
    }
}