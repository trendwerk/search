<?php
namespace Trendwerk\Search\Test;

use BadMethodCallException;
use Mockery;
use Trendwerk\Search\Dimension\Meta;
use WP_Mock;

final class MetaTest extends TestCase
{
    private $tableAlias = 'searchMeta';
    private $wpdb;

    public function setUp()
    {
        parent::setUp();

        $this->wpdb = Mockery::mock('wpdb');
    }

    public function testKeyRequired()
    {
        $this->expectException(BadMethodCallException::class);
        $meta = new Meta($this->wpdb, []);
    }

    public function testJoin()
    {
        $tableAliasCount = 2;
        $tableAlias = $this->tableAlias . $tableAliasCount;

        $this->wpdb->posts = 'wp_posts';
        $this->wpdb->postmeta = 'wp_postmeta';

        $expectation = "INNER JOIN {$this->wpdb->postmeta} AS {$tableAlias}
            ON ({$this->wpdb->posts}.ID = {$tableAlias}.post_id)";

        $meta = $this->create('firstName', '=');

        $result = $meta->join($tableAliasCount);

        $this->assertEquals($expectation, $result);
    }

    public function testSearchEquals()
    {
        $this->search('Testman', 'firstName', '=');
    }

    public function testSearchLike()
    {
        $this->search('McTest', 'lastName', 'LIKE');
    }

    public function testSearchAliasCount()
    {
        $this->search('Testman', 'firstName', '=', 2);
    }

    private function search($searchWord, $metaKey, $compare, $tableAliasCount = 0)
    {
        $tableAlias = $this->tableAlias . $tableAliasCount;
        $expectation = "({$tableAlias}.meta_key {$compare} %s AND {$tableAlias}.meta_value LIKE %s)";

        $meta = $this->create($metaKey, $compare);

        $this->wpdb->shouldReceive('prepare')
            ->once()
            ->andReturnUsing(function () {
                return func_get_args();
            });

        $result = $meta->search($searchWord, $tableAliasCount);

        $this->assertEquals($result, [$expectation, $metaKey, $searchWord]);
    }

    private function create($metaKey, $compare)
    {
        WP_Mock::wpPassthruFunction('wp_parse_args', ['times' => 1]);

        $meta = new Meta($this->wpdb, [
            'compare' => $compare,
            'key'     => $metaKey,
        ]);

        return $meta;
    }
}
