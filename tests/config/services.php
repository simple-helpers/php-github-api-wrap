<?php

use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Db\Adapter\Pdo\Mysql as DatabaseConnection;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Mvc\Model\Metadata\Files as MetaDataAdapter;
use Phalcon\Mvc\Model\Metadata\Memory as MemoryMetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Cache\Backend\File as FileCache;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Cache\Frontend\None as FrontendNone;
use Phalcon\Cache\Backend\Memory as MemoryBackend;
use Phalcon\Cache\Frontend\Output as FrontendOutput;
use Github\Client as Github;
use Mockery as m;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Httpful\Request as HRequest;

//Real logger
/*
$di->set(
    'logger',
    function() use ($config) {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler($config->application->logDir . "php-github-api-wrap"));
        return $logger;
    },
    true
);
 */

$di->set(
    'logger',
    function() use ($config) {
        $logger = m::mock("Logger");
        $logger->shouldReceive("addDebug","addError")->andReturn(true);

        return $logger;
    },
    true
);

//Real httpful service
/*
$dispatcher->set('httpful', function($method, $url = null) use ($config){
    $response = HRequest::$method($url)->send();

    return $response;
}, true);
 */

//httpful mock

$di->set('httpful', function($method, $url = null) use ($config,$di){
    switch($method)
    {
    case 'get':
        $response = m::mock("HRequest");
        $fileLink = __DIR__."/../_data/" . basename($url);
        $response->raw_body = file_get_contents($fileLink);

        break;

    default:
        break;
    }

    return $response;
}, false);

// Real Github API service
/*
$di->set(
    'github',
    function() use ($config) {
        $client = new Github();
        return $client;
    },
    true
);*/

//Github API mock

$di->set(
    'github',
    function() use ($config) {
        $api = m::mock("api");
        $api->shouldReceive("show")
            ->with("simple-helpers","php-file-mover",8)
            ->andReturn(json_decode(file_get_contents(__DIR__."/../_data/8"),true));

        $client = m::mock("Github");

        $client->shouldReceive("api")->with('pull_request')->andReturn($api);
        return $client;
    },
    true
);

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set(
    'url',
    function () use ($config) {
        $url = new UrlResolver();
        if (!$config->application->debug) {
            $url->setBaseUri($config->application->production->baseUri);
            $url->setStaticBaseUri($config->application->production->staticBaseUri);
        } else {
            $url->setBaseUri($config->application->development->baseUri);
            $url->setStaticBaseUri($config->application->development->staticBaseUri);
        }
        return $url;
    },
    true
);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set(
    'db',
    function () use ($config) {

        $connection = new DatabaseConnection($config->database->toArray());

        $debug = $config->application->debug;
        if ($debug) {

            $eventsManager = new EventsManager();

            $logger = new FileLogger(APP_PATH . "/app/logs/db.log");

            //Listen all the database events
            $eventsManager->attach(
                'db',
                function ($event, $connection) use ($logger) {
                    /** @var Phalcon\Events\Event $event */
                    if ($event->getType() == 'beforeQuery') {
                        /** @var DatabaseConnection $connection */
                        $variables = $connection->getSQLVariables();
                        if ($variables) {
                            $logger->log($connection->getSQLStatement() . ' [' . join(',', $variables) . ']', \Phalcon\Logger::INFO);
                        } else {
                            $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
                        }
                    }
                }
            );

            //Assign the eventsManager to the db adapter instance
            $connection->setEventsManager($eventsManager);
        }

        return $connection;
    }
);


/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set(
    'modelsMetadata',
    function () use ($config) {

        if ($config->application->debug) {
            return new MemoryMetaDataAdapter();
        }

        return new MetaDataAdapter(['metaDataDir' => APP_PATH . '/app/cache/metaData/']);

    },
    true
);

/**
 * Start the session the first time some component request the session service
 */
$di->set(
    'session',
    function () {
        $session = new SessionAdapter();
        $session->start();
        return $session;
    },
    true
);

/**
 * Router
 */
$di->set(
    'router',
    function () {
        return include APP_PATH . "/app/config/routes.php";
    },
    true
);

/**
 * Register the configuration itself as a service
 */
$di->set('config', $config);

$di->set(
    'dispatcher',
    function () {
        $dispatcher = new MvcDispatcher();
        $dispatcher->setDefaultNamespace('Phosphorum\Controllers');
        return $dispatcher;
    },
    true
);

/**
 * View cache
 */
$di->set(
    'viewCache',
    function () use ($config) {

        if ($config->application->debug) {

            $frontCache = new FrontendNone();
            return new MemoryBackend($frontCache);

        } else {
            //Cache data for one day by default
            $frontCache = new FrontendOutput(["lifetime" => 86400 * 30]);

            return new FileCache($frontCache, [
                "cacheDir" => APP_PATH . "/app/cache/views/",
                "prefix"   => "forum-cache-"
            ]);
        }
    }
);

/**
 * Cache
 */
$di->set(
    'modelsCache',
    function () use ($config) {

        if ($config->application->debug) {

            $frontCache = new FrontendNone();
            return new MemoryBackend($frontCache);

        } else {

            //Cache data for one day by default
            $frontCache = new \Phalcon\Cache\Frontend\Data(["lifetime" => 86400 * 30]);

            return new \Phalcon\Cache\Backend\File($frontCache, [
                "cacheDir" => APP_PATH . "/app/cache/data/",
                "prefix"   => "forum-cache-data-"
            ]);
        }
    }
);
