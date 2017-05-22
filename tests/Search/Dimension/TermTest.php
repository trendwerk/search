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
}
