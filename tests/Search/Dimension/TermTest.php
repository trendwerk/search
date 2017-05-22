<?php
namespace Trendwerk\Search\Test;

use BadMethodCallException;
use Mockery;
use Trendwerk\Search\Dimension\Term;
use WP_Mock;

final class TermTest extends TestCase
{
    private $tableAlias = 'searchTerm';
    private $wpdb;

    public function setUp()
    {
        parent::setUp();

        $this->wpdb = Mockery::mock('wpdb');
        $this->wpdb->posts = 'wp_posts';
        $this->wpdb->term_relationships = 'wp_term_relationships';
        $this->wpdb->term_taxonomy = 'wp_term_taxonomy';
        $this->wpdb->terms = 'wp_terms';
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

        $expectation = "INNER JOIN {$this->wpdb->term_relationships} AS {$tableAlias} ";
        $expectation .= "ON ({$this->wpdb->posts}.ID = {$tableAlias}.object_id)";

        $term = $this->create('taxonomyName');

        $result = $term->join($tableAliasCount);

        $this->assertEquals($expectation, $result);
    }

    public function testSearch()
    {
        $this->search('Testterm', 'testTaxonomy');
    }

    private function search($searchWord, $taxonomy, $tableAliasCount = 0)
    {
        $tableAlias = $this->tableAlias . $tableAliasCount;
        $termIds = [18, 12];
        $expectation = "{$tableAlias}.term_taxonomy_id IN (18,12)";

        $term = $this->create($taxonomy);

        WP_Mock::wpPassthruFunction('absint', ['times' => 2]);

        $this->wpdb->shouldReceive('esc_like')
            ->once()
            ->with($searchWord)
            ->andReturn($searchWord);

        $this->wpdb->shouldReceive('prepare')
            ->once()
            ->andReturnUsing(function ($sql, $taxonomy, $search) {
                return sprintf($sql, $taxonomy, $search);
            });

        $this->wpdb->shouldReceive('get_col')
            ->once()
            ->andReturn($termIds);

        $result = $term->search($searchWord, $tableAliasCount);

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
