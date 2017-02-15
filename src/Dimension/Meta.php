<?php
namespace Trendwerk\Search\Dimension;

use BadMethodCallException;
use wpdb;

final class Meta implements Dimension
{
    private $options;
    private $tableAlias = 'searchMeta';
    private $wpdb;

    public function __construct(wpdb $wpdb, array $options)
    {
        if (! isset($options['key'])) {
            throw new BadMethodCallException('`key` is a required property.');
        }

        $this->options = wp_parse_args($options, [
            'compare' => '=',
        ]);
        $this->wpdb = $wpdb;
    }

    public function join($aliasCount = 0)
    {
        $tableAlias = $this->tableAlias . $aliasCount;

        return "INNER JOIN {$this->wpdb->postmeta} AS {$tableAlias} ON ({$this->wpdb->posts}.ID = {$tableAlias}.post_id)";
    }

    public function search($searchWord, $aliasCount = 0)
    {
        $tableAlias = $this->tableAlias . $aliasCount;

        $searchSql = "({$tableAlias}.meta_key {$this->options['compare']} %s";
        $searchSql .= " AND ";
        $searchSql .= "{$tableAlias}.meta_value LIKE %s)";

        return $this->wpdb->prepare($searchSql, $this->options['key'], $searchWord);
    }
}
