<?php
namespace Trendwerk\Search\Test;

use Mockery;
use Trendwerk\Search\Dimension\Dimensions;
use Trendwerk\Search\Dimension\Meta;
use Trendwerk\Search\Hook\Posts;
use WP_Mock;

final class PostsTest extends TestCase
{
    private $posts;

    public function setUp()
    {
        parent::setUp();

        $wpdb = Mockery::mock('wpdb');

        $dimensions = new Dimensions();
        $dimensions->add(new Meta($wpdb, [
            'key' => 'lastName',
        ]));
        
        $this->posts = new Posts($dimensions);
    }

    public function testInit()
    {
        $argsCount = 2;
        $priority = 10;

        WP_Mock::expectFilterAdded('posts_distinct', [$this->posts, 'distinct'], $priority, $argsCount);
        WP_Mock::expectFilterAdded('posts_join', [$this->posts, 'join'], $priority, $argsCount);
        WP_Mock::expectFilterAdded('posts_search', [$this->posts, 'search'], $priority, $argsCount);

        $this->posts->init();

        WP_Mock::assertHooksAdded();
    }

    public function testDistinct()
    {
        $expectation = "DISTINCT";

        $wpQuery = Mockery::mock('WP_Query');
        $wpQuery->is_search = true;
        $wpQuery->shouldReceive('get')
            ->with('search_terms')
            ->andReturn(['Testman', 'mcTest']);

        $result = $this->posts->distinct('', $wpQuery);

        $this->assertEquals($expectation, $result);
    }
}
