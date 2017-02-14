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

        add_filter('posts_distinct', [$this, 'distinct'], $defaultPriority, $acceptedArgs);
        add_filter('posts_join', [$this, 'join'], $defaultPriority, $acceptedArgs);
        add_filter('posts_search', [$this, 'search'], $defaultPriority, $acceptedArgs);
    }

    public function distinct($sql, WP_Query $query)
    {
        if (! $query->is_search) {
            return $sql;
        }

        return 'DISTINCT';
    }

    public function join($sql, WP_Query $query)
    {
        if (! $query->is_search) {
            return $sql;
        }

        $joins = [];

        foreach ($this->dimensions->get() as $dimension) {
            $dimensionType = get_class($dimension);
            $joins[$dimensionType] = $dimension->join($this->wpdb);
        }

        return $sql . ' ' . implode(' ', $joins);
    }

    public function search($sql, WP_Query $query)
    {
        if (! $query->is_search) {
            return $sql;
        }

        $searchWords = $query->get('search_terms');

        $searches = [];

        foreach ($this->dimensions->get() as $dimension) {
            $searches[] = $dimension->search($this->wpdb, $searchWords);
        }

        if ($searches) {
            $metaSearch = implode(' OR ', $searches);
        }

        if ($metaSearch) {
            $and = ' AND ';
            $sql = preg_replace('/' . $and . '/', $and . '(', $sql, 1);
            $sql .= ' OR (' . $metaSearch . '))';
        }

        return $sql;
    }
}
