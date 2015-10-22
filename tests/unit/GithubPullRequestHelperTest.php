<?php

use SimpleHelpers\Libs\Github\GithubAPIWrap as GithubAPIWrap;

class GithubPullRequestHelperTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $api;

    protected function _before()
    {
        $this->api = new GithubAPIWrap();
    }

    protected function _after()
    {
    }

    // tests
    public function testFetchPullRequestData()
    {

        $this->assertTrue( is_object($this->api->fetchPullRequestData("simple-helpers", "php-file-mover", 8)) , "Check if api returns object to allow chain calls" );
        $this->assertTrue( is_array($this->api->showPullRequestData()), "Check if request is fetched, decoded and stored" );
        $this->assertGreaterThan( 0, count($this->api->showPullRequestData()) , "Check if pull contents data");
    }

    public function testFetchPullCommitsData()
    {
        $this->api->fetchPullRequestData("simple-helpers", "php-file-mover", 8);

        $this->assertTrue( is_object($this->api->fetchPullRequestCommitsData()), "Check if api returns object to allow chain calls" );
        $this->assertTrue( is_array($this->api->showPullRequestCommitsData()), "Check if JSON fetched, decoded, stored correctly and being accessible." );
        $this->assertGreaterThan( 0, count($this->api->showPullRequestCommitsData()), "Check if commits array contains data" );

    }

    public function testFetchPullRequestCommitsLinks()
    {
        $this->api->fetchPullRequestData("simple-helpers", "php-file-mover", 8)
            ->fetchPullRequestCommitsData();

        $this->assertTrue( is_object($this->api->fetchPullRequestCommitsLinks()), "Check if api returns object to allow chain calls" );
        $this->assertTrue( is_array($this->api->showPullRequestCommitsLinks()), "Check if links fetched and stored correctly and being accessible." );
        $this->assertGreaterThan( 0, count($this->api->showPullRequestCommitsLinks()), "Check if links array contains data" );
    }

    public function testFetchPullRequestFilesLinks()
    {
        $this->api->fetchPullRequestData("simple-helpers", "php-file-mover", 8)
            ->fetchPullRequestCommitsData()
            ->fetchPullRequestCommitsLinks();

        $this->assertTrue( is_object($this->api->fetchPullRequestFilesLinks()), "Check if api returns object to allow chain calls" );
        $this->assertTrue( is_array($this->api->showPullRequestFilesLinks()), "Check if files links fetched and stored correctly and being accessible." );
        $this->assertGreaterThan( 0, count($this->api->showPullRequestFilesLinks()), "Check if links array contains data" );
    }

    public function testFetchPullRequestFiles()
    {
        $this->api->fetchPullRequestData("simple-helpers", "php-file-mover", 8)
            ->fetchPullRequestCommitsData()
            ->fetchPullRequestCommitsLinks()
            ->fetchPullRequestFilesLinks();

        $this->assertTrue( is_object($this->api->fetchPullRequestFiles()), "Check if api returns object to allow chain calls" );
        $this->assertTrue( is_array($this->api->showPullRequestFiles()), "Check if files fetched and stored correctly and being accessible." );
        $this->assertGreaterThan( 0, count($this->api->showPullRequestFiles()), "Check if files array contains data" );

    }
}
