<?php
namespace Trendwerk\Search\Dimension;

use BadMethodCallException;
use wpdb;

final class Term implements Dimension
{
    private $options;
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
        return '';
    }

    public function search($searchWord, $aliasCount = 0)
    {
        return '0=1';
    }
}
