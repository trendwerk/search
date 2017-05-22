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
        $tableAlias = $this->tableAlias . $aliasCount;
        $searchWord = $this->wpdb->esc_like($searchWord);

        $termIds = array_map('absint', $this->termsFor($searchWord));

        if (count($termIds) == 0) {
            return;
        }

        $termIds = implode(',', $termIds);

        return "{$tableAlias}.term_taxonomy_id IN ({$termIds})";
    }

    private function termsFor(string $searchWord)
    {
        return $this->wpdb->get_col($this->wpdb->prepare("SELECT {$this->wpdb->term_taxonomy}.term_id
            FROM {$this->wpdb->term_taxonomy}
            INNER JOIN {$this->wpdb->terms} USING(term_id)
            WHERE taxonomy = %s
            AND {$this->wpdb->terms}.name LIKE %s
        ", $this->options['taxonomy'], "%{$searchWord}%"));
    }
}
