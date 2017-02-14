<?php
namespace Trendwerk\Search\Dimension;

use wpdb;

interface Dimension
{
    public function join(wpdb $wpdb);
}
