<?php
namespace Trendwerk\Search\Test;

use PHPUnit_Framework_TestCase;
use WP_Mock;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        WP_Mock::setUp();
    }

    public function tearDown()
    {
        WP_Mock::tearDown();
    }
}
