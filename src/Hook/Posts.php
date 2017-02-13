<?php
namespace Trendwerk\Search\Hook;

use wpdb;

final class Posts
{
    private $dimensions;
    private $wpdb;

    public function __construct(wpdb $wpdb, $dimensions)
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

    public function join($sql, $query)
    {
        return $sql;
    }

    public function search($sql, $query)
    {
        return $sql;
    }
}
