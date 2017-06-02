<?php
namespace Trendwerk\Search\Test;

use Mockery;
use Trendwerk\Search\Dimension\Dimensions;
use Trendwerk\Search\Dimension\Meta;
use Trendwerk\Search\Dimension\Term;
use Trendwerk\Search\Hook\Posts;
use WP_Mock;

final class PostsTest extends TestCase
{
    private $metaKey = 'lastName';
    private $posts;
    private $taxonomy = 'taxonomyName';
    private $wpdb;

    public function setUp()
    {
        parent::setUp();

        $this->wpdb = Mockery::mock('wpdb');
        $this->wpdb->postmeta = 'wp_postmeta';
        $this->wpdb->posts = 'wp_posts';
        $this->wpdb->term_relationships = 'wp_term_relationships';
        $this->wpdb->term_taxonomy = 'wp_term_taxonomy';
        $this->wpdb->terms = 'wp_terms';

        $dimensions = new Dimensions();
        $dimensions->add(new Meta($this->wpdb, [
            'compare' => '=',
            'key'     => $this->metaKey,
        ]));
        $dimensions->add(new Term($this->wpdb, [
            'taxonomy' => $this->taxonomy,
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
            $metaAlias = 'searchMeta' . $index;
            $termAlias = 'searchTerm' . $index;

            $wordExpectation = "INNER JOIN {$this->wpdb->postmeta} AS {$metaAlias} ";
            $wordExpectation .= "ON ({$this->wpdb->posts}.ID = {$metaAlias}.post_id) ";
            $wordExpectation .= "INNER JOIN {$this->wpdb->term_relationships} AS {$termAlias} ";
            $wordExpectation .= "ON ({$this->wpdb->posts}.ID = {$termAlias}.object_id)";

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

    public function testSearch()
    {
        $and = " AND ";
        $or = " OR ";

        $searchTerms = ['Testman', 'theTester'];
        $fakeTermIds = [1, 9];
        $baseSql = $and . "(";

        foreach ($searchTerms as $searchTerm) {
            $baseSql .= "(";
            $baseSql .= "({$this->wpdb->posts}.post_title LIKE '%{$searchTerm}%')";
            $baseSql .= $or;
            $baseSql .= "({$this->wpdb->posts}.post_content LIKE '%{$searchTerm}%')";
            $baseSql .= ")" . $and;
        }

        $baseSql = mb_substr($baseSql, 0, mb_strlen($baseSql) - mb_strlen($or));
        $baseSql .= ")";

        $expectations = [];

        foreach ($searchTerms as $index => $searchTerm) {
            $expectations[] = "searchMeta{$index}.meta_key  %s AND searchMeta{$index}.meta_value LIKE %s";
            $termIds = implode(',', $fakeTermIds);
            $expectations[] = "searchTerm{$index}.term_taxonomy_id IN ({$termIds})";
        }

        WP_Mock::wpPassthruFunction('absint', ['times' => (count($fakeTermIds) * count($searchTerms))]);

        $this->wpdb->shouldReceive('esc_like')
            ->times((count($searchTerms) * 2))
            ->andReturnUsing(function ($searchWord) {
                return $searchWord;
            });

        $this->wpdb->shouldReceive('prepare')
            ->times((count($searchTerms) * 2))
            ->andReturnUsing(function ($sql) {
                return $sql;
            });


        $this->wpdb->shouldReceive('get_col')
            ->times(count($searchTerms))
            ->andReturn($fakeTermIds);

        $result = $this->posts->search($baseSql, $this->getQuery(true, $searchTerms));

        foreach ($expectations as $expectation) {
            $this->assertContains($expectation, $result);
        }
    }

    public function testSearchWithoutSearch()
    {
        $expectation = '';
        $result = $this->posts->search('', $this->getQuery(false));

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
