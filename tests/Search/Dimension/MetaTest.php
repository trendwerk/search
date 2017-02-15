<?php
namespace Trendwerk\Search\Test;

use BadMethodCallException;
use Mockery;
use Trendwerk\Search\Dimension\Meta;
use WP_Mock;

final class MetaTest extends TestCase
{
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

    public function testSearchEquals()
    {
        $this->search('Testman', 'firstName', '=');
    }

    public function testSearchLike()
    {
        $this->search('McTest', 'lastName', 'LIKE');
    }

    private function search($searchWord, $metaKey, $compare)
    {
        $expectation = "(searchMeta0.meta_key {$compare} %s AND searchMeta0.meta_value LIKE %s)";

        WP_Mock::wpPassthruFunction('wp_parse_args', ['times' => 1]);

        $meta = new Meta($this->wpdb, [
            'compare' => $compare,
            'key'     => $metaKey,
        ]);

        $this->wpdb->shouldReceive('prepare')
            ->once()
            ->andReturnUsing(function () {
                return func_get_args();
            });

        $result = $meta->search($searchWord);

        $this->assertEquals($result, [$expectation, $metaKey, $searchWord]);
    }
}
