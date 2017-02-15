<?php
namespace Trendwerk\Search\Test;

use BadMethodCallException;
use Mockery;
use Trendwerk\Search\Dimension\Meta;
use Trendwerk\Search\Search;
use WP_Mock;

final class SearchTest extends TestCase
{
    private $search;

    public function setUp()
    {
        parent::setUp();

        $this->search = new Search();
    }

    public function testInit()
    {
        $this->assertNull($this->search->init());
    }

    public function testAddDimension()
    {
        $wpdb = Mockery::mock('\wpdb');

        $this->expectException(BadMethodCallException::class);
        $this->search->addDimension(new Meta($wpdb, []));
    }
}
