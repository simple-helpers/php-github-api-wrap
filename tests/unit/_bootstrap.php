<?php
// Here you can initialize variables that will be available to your tests
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;
use Codeception\Util\Fixtures;
use Codeception\Util\Autoload;

defined('APP_PATH') || define('APP_PATH', dirname(dirname(dirname(__FILE__))));
defined('CONFIG_PATH') || define('CONFIG_PATH', (dirname(dirname(__FILE__))));
require APP_PATH . "/vendor/autoload.php";

$config = include CONFIG_PATH . "/config/config.php";

$di = new FactoryDefault;

require CONFIG_PATH . "/config/services.php";

$application = new Application;
$application->setDI($di);

Fixtures::add("app", $application);
Autoload::addNamespace('SimpleHelpers\Libs\Github', '/src/');
