<?php
namespace Trendwerk\Search\Dimension;

use wpdb;

final class Meta implements Dimension
{
    private $tableAlias = 'searchMeta';

    public function join(wpdb $wpdb)
    {
        return 'INNER JOIN ' . $wpdb->postmeta . ' AS ' . $this->tableAlias .
        ' ON (' . $wpdb->posts . '.ID = ' . $this->tableAlias . '.post_id)';
    }
}
