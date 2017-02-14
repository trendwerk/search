<?php
namespace Trendwerk\Search\Dimension;

use wpdb;

interface Dimension
{
    public function __construct(array $options);
    public function join(wpdb $wpdb);
    public function search(wpdb $wpdb, array $words);
}
