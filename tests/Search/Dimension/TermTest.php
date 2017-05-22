<?php
namespace Trendwerk\Search\Test;

use BadMethodCallException;
use Mockery;
use Trendwerk\Search\Dimension\Term;

final class TermTest extends TestCase
{
    private $tableAlias = 'searchTerm';
    private $wpdb;

    public function setUp()
    {
        parent::setUp();

        $this->wpdb = Mockery::mock('wpdb');
    }

    public function testKeyRequired()
    {
        $this->expectException(BadMethodCallException::class);
        $meta = new Term($this->wpdb, []);
    }

    public function testJoin()
    {
        $tableAliasCount = 2;
        $tableAlias = $this->tableAlias . $tableAliasCount;

        $this->wpdb->posts = 'wp_posts';
        $this->wpdb->term_relationships = 'wp_term_relationships';

        $expectation = "INNER JOIN {$this->wpdb->term_relationships} AS {$tableAlias} ";
        $expectation .= "ON ({$this->wpdb->posts}.ID = {$tableAlias}.object_id)";

        $term = $this->create('taxonomyName');

        $result = $term->join($tableAliasCount);

        $this->assertEquals($expectation, $result);
    }

    private function create($taxonomy)
    {
        $term = new Term($this->wpdb, [
            'taxonomy' => $taxonomy,
        ]);

        return $term;
    }
}
