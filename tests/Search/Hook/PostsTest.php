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
        $result = $this->posts->distinct('', $this->getQuery());

        $this->assertEquals($expectation, $result);
    }

    public function testDistinctNotSearch()
    {
        $expectation = '';
        $result = $this->posts->distinct('', $this->getQuery(false));

        $this->assertEquals($expectation, $result);
    }

    public function testDistinctNoWords()
    {
        $expectation = '';
        $result = $this->posts->distinct('', $this->getQuery(true, []));

        $this->assertEquals($expectation, $result);
    }

    private function getQuery($isSearch = true, $terms = ['Testman', 'mcTest'])
    {
        $wpQuery = Mockery::mock('WP_Query');
        $wpQuery->is_search = $isSearch;
        $wpQuery->shouldReceive('get')
            ->with('search_terms')
            ->andReturn($terms);

        return $wpQuery;
    }
}
