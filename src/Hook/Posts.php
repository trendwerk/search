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

        return "DISTINCT";
    }

    public function join($sql, WP_Query $query)
    {
        if (! $query->is_search) {
            return $sql;
        }

        $searchWords = $query->get('search_terms');

        $joins = [];

        foreach ($searchWords as $wordCount => $searchWord) {
            foreach ($this->dimensions->get() as $dimension) {
                $dimensionType = get_class($dimension);
                $joins[$dimensionType . $wordCount] = $dimension->join($this->wpdb, $wordCount);
            }
        }

        return $sql . ' ' . implode(' ', $joins);
    }

    public function search($sql, WP_Query $query)
    {
        if (! $query->is_search) {
            return $sql;
        }

        $and = " AND ";
        $or = " OR ";

        $searchWords = $query->get('search_terms');
        $andClauses = array_values(array_filter(explode($and, $sql)));

        foreach ($andClauses as $index => &$clause) {
            $searchWord = $searchWords[$index];
            $searches = [];

            foreach ($this->dimensions->get() as $dimension) {
                $searches[] = $dimension->search($this->wpdb, $searchWord, $index);
            }

            $search = '(' . implode($or, $searches) . ')' . $or;

            $clause = preg_replace('/' . $or . '/', $or . $search, $clause, 1);
        }

        return $and . implode($and, $andClauses);
    }
}
