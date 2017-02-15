<?php
namespace Trendwerk\Search\Dimension;

use wpdb;

interface Dimension
{
    public function __construct(wpdb $wpdb, array $options);
    public function join($aliasCount = 0);
    public function search($searchWord, $aliasCount = 0);
}
