<?php
namespace Trendwerk\Search\Dimension;

use BadMethodCallException;
use wpdb;

final class Term implements Dimension
{
    private $options;
    private $tableAlias = 'searchTerm';
    private $wpdb;

    public function __construct(wpdb $wpdb, array $options)
    {
        if (! isset($options['taxonomy'])) {
            throw new BadMethodCallException('`taxonomy` is a required property.');
        }

        $this->options = $options;
        $this->wpdb = $wpdb;
    }

    public function join($aliasCount = 0)
    {
        $tableAlias = $this->tableAlias . $aliasCount;

        $sql = "INNER JOIN {$this->wpdb->term_relationships} AS {$tableAlias} ";
        $sql .= "ON ({$this->wpdb->posts}.ID = {$tableAlias}.object_id)";

        return $sql;
    }

    public function search($searchWord, $aliasCount = 0)
    {
        return '0=1';
    }
}
