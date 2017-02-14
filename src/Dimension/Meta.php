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

        $this->options = $options;
    }

    public function join(wpdb $wpdb)
    {
        return 'INNER JOIN ' . $wpdb->postmeta . ' AS ' . $this->tableAlias .
        ' ON (' . $wpdb->posts . '.ID = ' . $this->tableAlias . '.post_id)';
    }

    public function search(wpdb $wpdb, array $words)
    {
        return '(' . $this->tableAlias . '.meta_key = \'' . $this->options['key'] . '\' AND ' .
        $this->tableAlias . '.meta_value REGEXP \'' . implode('|', $words) . '\')';
    }
}
