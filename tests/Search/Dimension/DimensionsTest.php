<?php
namespace Trendwerk\Search\Test;

use Mockery;
use Trendwerk\Search\Dimension\Dimensions;
use Trendwerk\Search\Dimension\Meta;
use WP_Mock;

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
        $wpdb = Mockery::mock('wpdb');

        WP_Mock::wpPassthruFunction('wp_parse_args', ['times' => 2]);

        $metaSearches = [
            new Meta($wpdb, [
                'key' => 'firstName',
            ]),
            new Meta($wpdb, [
                'key' => 'lastName',
            ]),
        ];

        foreach ($metaSearches as $metaSearch) {
            $this->assertNull($this->dimensions->add($metaSearch));
        }

        $this->assertEquals($this->dimensions->get(), $metaSearches);
    }
}
