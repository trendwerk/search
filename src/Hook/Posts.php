<?php
namespace Trendwerk\Search\Hook;

use Trendwerk\Search\Dimension\Dimension;
use Trendwerk\Search\Dimension\Dimensions;
use WP_Query;
use wpdb;

final class Posts
{
    private $dimensions;
    private $wpdb;

    public function __construct(wpdb $wpdb, Dimensions $dimensions)
    {
        $this->dimensions = $dimensions;
        $this->wpdb = $wpdb;
    }

    public function init()
    {
        $acceptedArgs = 2;
        $defaultPriority = 10;

        add_filter('posts_join', [$this, 'join'], $defaultPriority, $acceptedArgs);
        add_filter('posts_search', [$this, 'search'], $defaultPriority, $acceptedArgs);
    }

    public function join($sql, WP_Query $query)
    {
        if (! $query->is_search) {
            return $sql;
        }

        $joins = $this->forDimensions(function (Dimension $dimension) {
            return $dimension->join($this->wpdb);
        });

        return $sql . ' ' . implode(' ', $joins);
    }

    public function search($sql, WP_Query $query)
    {
        return $sql;
    }

    private function forDimensions($callback)
    {
        $results = [];

        foreach ($this->dimensions->get() as $dimension) {
            $dimensionType = get_class($dimension);
            $results[$dimensionType] = $callback($dimension);
        }

        return array_values($results);
    }
}
