<?php
namespace Trendwerk\Search\Dimension;

use wpdb;

interface Dimension
{
    public function __construct(array $options);
    public function join(wpdb $wpdb, $aliasCount = 0);
    public function search(wpdb $wpdb, $searchWord, $aliasCount = 0);
}
