[![Build Status](https://travis-ci.org/simple-helpers/php-github-api-wrap.svg)](https://travis-ci.org/simple-helpers/php-github-api-wrap)
# PHP Github API wrapper.

Simple [Phalcon](https://phalconphp.com) based wrapper for [KnpLabs/php-github-api](https://github.com/KnpLabs/php-github-api) providing some useful data fetching.

## Basic usage

Fetch & keep all commits and file links and the files of particular pull-request.
```php
<?php

// This file is generated by Composer
require_once 'vendor/autoload.php';

$api = new GithubAPIWrap();

$api->fetchPullRequestData("simple-helpers", "php-github-api-wrap", 1)
  ->fetchPullRequestCommitsData();
  ->fetchPullRequestCommitsLinks();
  ->fetchPullRequestFilesLinks();
  ->fetchPullRequestFiles();
```

