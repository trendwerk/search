<?php
namespace Trendwerk\Search\Test;

use Mockery;
use Trendwerk\Search\Dimension\Dimensions;
use Trendwerk\Search\Dimension\Meta;

final class DimensionsTest extends TestCase
{
    private $dimensions;

    public function setUp()
    {
        parent::setUp();

        $this->dimensions = new Dimensions();
    }

    public function testGetSet()
    {
        // $wpdb = Mockery::mock('\wpdb');
        // $metaSearches = [
        //     new Meta(),
        //     new Meta()
        // ];
    }
}
