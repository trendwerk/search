<?php
namespace Trendwerk\Search\Hook;

use Trendwerk\Search\Dimension\Dimension;
use Trendwerk\Search\Dimension\Dimensions;
use WP_Query;

final class Posts
{
    private $dimensions;

    public function __construct(Dimensions $dimensions)
    {
        $this->dimensions = $dimensions;
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
        if (! $this->isSearch($query)) {
            return $sql;
        }

        return "DISTINCT";
    }

    public function join($sql, WP_Query $query)
    {
        if (! $this->isSearch($query)) {
            return $sql;
        }

        $searchWords = $query->get('search_terms');

        $joins = [];

        foreach ($searchWords as $wordCount => $searchWord) {
            foreach ($this->dimensions->get() as $dimension) {
                $dimensionType = get_class($dimension);
                $joins[$dimensionType . $wordCount] = $dimension->join($wordCount);
            }
        }

        return $sql . ' ' . implode(' ', $joins);
    }

    public function search($sql, WP_Query $query)
    {
        if (! $this->isSearch($query)) {
            return $sql;
        }

        $and = " AND ";
        $or = " OR ";

        $searchWords = $query->get('search_terms');
        $andClauses = array_values(array_filter(explode($and, $sql)));

        foreach ($andClauses as $index => &$clause) {
            if (! isset($searchWords[$index])) {
                continue;
            }

            $searchWord = $searchWords[$index];
            $searches = [];

            foreach ($this->dimensions->get() as $dimension) {
                $searches[] = $dimension->search($searchWord, $index);
            }

            $searches = array_filter($searches);

            if (count($searches) === 0) {
                continue;
            }

            $search = '(' . implode($or, $searches) . ')' . $or;

            $clause = preg_replace('/' . $or . '/', $or . $search, $clause, 1);
        }

        return $and . implode($and, $andClauses);
    }

    private function isSearch(WP_Query $query)
    {
        if (! $query->is_search) {
            return false;
        }

        $searchWords = $query->get('search_terms');

        if (! $searchWords) {
            return false;
        }

        return true;
    }
}
