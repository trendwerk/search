<?php
namespace Trendwerk\Search\Test;

use BadMethodCallException;
use Mockery;
use Trendwerk\Search\Dimension\Meta;

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
}
