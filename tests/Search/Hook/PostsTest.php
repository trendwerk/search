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
    private $wpdb;

    public function setUp()
    {
        parent::setUp();

        $this->wpdb = Mockery::mock('wpdb');
        $this->wpdb->postmeta = 'wp_postmeta';
        $this->wpdb->posts = 'wp_posts';

        $dimensions = new Dimensions();
        $dimensions->add(new Meta($this->wpdb, [
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

    public function testJoin()
    {
        $baseSql = 'INNER JOIN alreadyAvailableSql';

        $searchTerms = ['Testman', 'theTester'];
        $expectation = [];

        foreach ($searchTerms as $index => $searchTerm) {
            $tableAlias = 'searchMeta' . $index;

            $wordExpectation = "INNER JOIN {$this->wpdb->postmeta} AS {$tableAlias} ";
            $wordExpectation .= "ON ({$this->wpdb->posts}.ID = {$tableAlias}.post_id)";

            $expectation[] = $wordExpectation;
        }

        $expectation = $baseSql . ' ' . implode(' ', $expectation);
        $result = $this->posts->join($baseSql, $this->getQuery(true, $searchTerms));
        
        $this->assertEquals($expectation, $result);
    }

    public function testJoinWithoutSearch()
    {
        $baseSql = 'INNER JOIN alreadyAvailable';
        $expectation = $baseSql;
        $result = $this->posts->join($baseSql, $this->getQuery(false));

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
