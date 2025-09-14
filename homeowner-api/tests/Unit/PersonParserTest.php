<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\HomeownerParser;

class PersonParserTest extends TestCase
{
    protected HomeownerParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new HomeownerParser();
    }

    public function test_single_full_name()
    {
        $result = $this->parser->parse("Mr John Smith");

        $this->assertCount(1, $result);
        $this->assertEquals([
            'title' => 'Mr',
            'first_name' => 'John',
            'initial' => null,
            'last_name' => 'Smith',
        ], $result[0]);
    }

    public function test_single_initial()
    {
        $result = $this->parser->parse("Mr J. Smith");

        $this->assertCount(1, $result);
        $this->assertEquals([
            'title' => 'Mr',
            'first_name' => null,
            'initial' => 'J',
            'last_name' => 'Smith',
        ], $result[0]);
    }

    public function test_mr_and_mrs_shared_last_name()
    {
        $result = $this->parser->parse("Mr and Mrs Smith");

        $this->assertCount(2, $result);
        $this->assertEquals([
            'title' => 'Mr',
            'first_name' => null,
            'initial' => null,
            'last_name' => 'Smith',
        ], $result[0]);

        $this->assertEquals([
            'title' => 'Mrs',
            'first_name' => null,
            'initial' => null,
            'last_name' => 'Smith',
        ], $result[1]);
    }

    public function test_mr_and_mrs_ampersand()
    {
        $result = $this->parser->parse("Mr & Mrs Smith");

        $this->assertCount(2, $result);
        $this->assertEquals('Mr', $result[0]['title']);
        $this->assertEquals('Mrs', $result[1]['title']);
        $this->assertEquals('Smith', $result[0]['last_name']);
        $this->assertEquals('Smith', $result[1]['last_name']);
    }

    public function test_mixed_titles_and_names()
    {
        $result = $this->parser->parse("Mr John Smith and Ms Jane Doe");

        $this->assertCount(2, $result);
        $this->assertEquals([
            'title' => 'Mr',
            'first_name' => 'John',
            'initial' => null,
            'last_name' => 'Smith',
        ], $result[0]);

        $this->assertEquals([
            'title' => 'Ms',
            'first_name' => 'Jane',
            'initial' => null,
            'last_name' => 'Doe',
        ], $result[1]);
    }

    public function test_title_with_initial_and_last_name()
    {
        $result = $this->parser->parse("Dr P. Gunn");

        $this->assertCount(1, $result);
        $this->assertEquals([
            'title' => 'Dr',
            'first_name' => null,
            'initial' => 'P',
            'last_name' => 'Gunn',
        ], $result[0]);
    }
}
