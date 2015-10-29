<?php

/**
 * PHP GitHub API client wrapper.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_SimpleHelpers
 * @author   barantaran <yourchev@gmail.com>
 * @license  BSD Licence
 * @link     https://github.com/simple-helpers/php-github-api-wrap
 */

namespace SimpleHelpers\Libs\Github;

use Phalcon\Mvc\User\Component as Component;

/**
 * PHP GitHub API client wrapper.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_SimpleHelpers
 * @author   barantaran <yourchev@gmail.com>
 * @license  BSD Licence
 * @link     https://github.com/simple-helpers/php-github-api-wrap
 */

class GithubAPIWrap extends Component
{
    /*
     * Keeps particular pullrequest data
     *
     * @var array
     * */
    private $_pullRequestData;

    /*
     * Keeps pullrequest commits data
     *
     * @var array
     * */
    private $_pullRequestCommitsData;

    /*
     * Keeps Github API links for particular commits
     *
     * @var array
     * */
    private $_commitsLinks;

    /*
     * Keeps Github API links for all files of particular pull-request
     *
     * @var array
     * */
    private $_fileLinks;

    /*
     * Keeps all files of particular pull-request
     *
     * @var array
     * */
    private $_files;

    /*
     * Keeps last message of a class runtime
     *
     * @var string
     * */
    private $_message;

    /**
     * Gathers and keeps services as class members
     */
    public function __construct()
    {
        $this->githubAPI = $this->getDI()->getShared("github");
        $this->logger = $this->getDI()->getShared("logger");
        $this->_message = "Constructed";
    }

    /**
     * Fetches pull request data using Github API then keeps it as class member
     *
     * @param string $user       Github username
     * @param string $repo       Github repository name
     * @param string $pullRqstID Github pull request ID
     *
     * @return object
     * */
    public function fetchPullRequestData($user, $repo, $pullRqstID)
    {
        $this->logger->addDebug(
            "Fetching pull request data: {$user} {$repo} {$pullRqstID}"
        );

        $this->_pullRequestData = $this->githubAPI->api("pull_request")
            ->show($user, $repo, $pullRqstID);

        return $this;
    }

    /**
     * Fetches pull request commits data using Github API
     * than keeps it as class member.
     *
     * @return object
     * */
    public function fetchPullRequestCommitsData()
    {
        $response = false;

        if (!is_array($this->_pullRequestData)
            || !count($this->_pullRequestData) > 0
        ) {

            $this->_message = "No pull request data, try fetchPullRequestData first";
            $this->logger->addError($this->_message);

            return null;
        }

        $this->logger->addDebug(
            "Fetching pull request commits data: " .
            $this->_pullRequestData["_links"]["commits"]["href"]
        );
        if (is_array($this->_pullRequestData)) {
            $response = $this->getDI()->get(
                "httpful",
                [
                    'get',
                    $this->_pullRequestData["_links"]["commits"]["href"]
                ]
            );
        }
        if ($response) {
            $this->_pullRequestCommitsData = json_decode($response->raw_body, true);
            $this->logger->addDebug(
                "Fetched commits: " .
                count($this->_pullRequestCommitsData)
            );
        }

        return $this;
    }

    /**
     * Fetches Github API commits links of particular pull-request than keeps them
     *
     * @return object
     * */
    public function fetchPullRequestCommitsLinks()
    {
        if (!is_array($this->_pullRequestCommitsData)
            || !count($this->_pullRequestCommitsData) > 0
        ) {
            $this->_message
                = "No commits data, try fetchPullRequestCommitsData first";
            $this->logger->addError($this->_message);

            return null;
        }

        foreach ($this->_pullRequestCommitsData as $commitData) {
            $this->logger->addDebug("Fetching commit url: {$commitData['url']}");
            $this->_commitsLinks[] = $commitData["url"];
        }

        return $this;
    }

    /**
     * Fetches Github API links for all files all
     * over particular pull-request than keeps them
     *
     * @return object
     * */
    public function fetchPullRequestFilesLinks()
    {
        $commitsParticularData = array();

        if (!is_array($this->_commitsLinks) || !count($this->_commitsLinks) > 0) {
            $this->_message
                = "No links fetched, try fetchPullRequestCommitsLinks first";
            $this->logger->addError($this->_message);

            return null;
        }

        foreach ($this->_commitsLinks as $link) {
            $this->logger->addDebug("Fetching commit data from: {$link}");
            $response = $this->getDI()->get(
                'httpful',
                [
                    'get',
                    $link
                ]
            );
            $commitsParticularData = json_decode($response->raw_body, true);
            foreach ($commitsParticularData["files"] as $fileData) {
                $this->_fileLinks[] = $fileData["raw_url"];
                $this->logger->addDebug("Store file link: {$fileData['raw_url']}");
            }
        }

        return $this;
    }

    /**
     * Fetches all files of particular pull-request than keeps them
     *
     * @return object
     * */
    public function fetchPullRequestFiles()
    {
        if (!is_array($this->_fileLinks) || !count($this->_fileLinks) > 0) {
            $this->_message
                = "No links fetched, try fetchPullRequestsFilesLinks first";
            $this->logger->addError($this->_message);

            return null;
        }

        foreach ($this->_fileLinks as $link) {
            $this->logger->addDebug("Fetching file from: {$link}");
            $response = $this->getDI()->get(
                'httpful',
                [
                    'get',
                    $link
                ]
            );
            $this->_files[basename($link)] = $response->raw_body;
        }

        return $this;
    }

    /**
     * Returns pull-request fetched data
     *
     * @return array
     * */
    public function showPullRequestData()
    {
         return $this->_pullRequestData;
    }

    /**
     * Returns commits data for pull-request fetched
     *
     * @return array
     * */
    public function showPullRequestCommitsData()
    {
        return $this->_pullRequestCommitsData;
    }

    /**
     * Returns Github API links for all commits of pull-request fetched
     *
     * @return array
     * */
    public function showPullRequestCommitsLinks()
    {
        return $this->_commitsLinks;
    }

    /**
     * Returns Github API links for all commits of pull-request fetched
     *
     * @return array
     * */
    public function showPullRequestFilesLinks()
    {
        return $this->_fileLinks;
    }

    /**
     * Returns files of pull-request fetched
     *
     * @return array
     * */
    public function showPullRequestFiles()
    {
        return $this->_files;
    }

    /**
     * Returns last message of this
     *
     * @return array
     * */
    public function showMessage()
    {
        return $this->_message;
    }
}
