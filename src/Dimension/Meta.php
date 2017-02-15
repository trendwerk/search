<?php
namespace Trendwerk\Search\Dimension;

use BadMethodCallException;
use wpdb;

final class Meta implements Dimension
{
    private $options;
    private $tableAlias = 'searchMeta';

    public function __construct(array $options)
    {
        if (! isset($options['key'])) {
            throw new BadMethodCallException('`key` is a required property.');
        }

        $this->options = wp_parse_args($options, [
            'compare' => '=',
        ]);
    }

    public function join(wpdb $wpdb, $aliasCount = 0)
    {
        $tableAlias = $this->tableAlias . $aliasCount;

        return 'INNER JOIN ' . $wpdb->postmeta . ' AS ' . $tableAlias .
            ' ON (' . $wpdb->posts . '.ID = ' . $tableAlias . '.post_id)';
    }

    public function search(wpdb $wpdb, $searchWord, $aliasCount = 0)
    {
        $tableAlias = $this->tableAlias . $aliasCount;

        return '(' . $tableAlias . '.meta_key = \'' . $this->options['key'] . '\' AND ' .
            $tableAlias . '.meta_value LIKE \'%' . $searchWord . '%\')';
    }
}
