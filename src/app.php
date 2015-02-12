<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\MonologServiceProvider;

class MyApplication extends Application
{
    use Silex\Application\MonologTrait;
}

$app = new MyApplication();
$app->register(new RoutingServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new MonologServiceProvider(), array(
    'monolog.logfile'   => __DIR__.'/../var/logs/app_criticals.log',
    'monolog.level'     => \Monolog\Logger::CRITICAL
));
$app->register(new Silex\Provider\SessionServiceProvider());

$app['session']->start();

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
        return $app['request_stack']->getMasterRequest()->getBasepath().'/'.$asset;
    }));

    return $twig;
});

$app['mongo.client']    = function($c) {
    $host = $c['mongo.host'];
    $client = new MongoClient($host);

    return $client;
};

$app['mongo.db']    = function($c) {
    $dbName = $c['mongo.db_name'];

    /** @var $client MongoClient */
    $client = $c['mongo.client'];

    return $client->$dbName;
};

$app['mongo.collection.signups'] = function($c) {
    $collectionName = 'signups';

    /** @var $db MongoDB */
    $db = $c['mongo.db'];

    return $db->selectCollection($collectionName);
};

$app['mongo.collection.logs.name']   = 'logs';

$app['monolog'] = $app->extend('monolog', function($monolog, $app) {
    $client         = $app['mongo.client'];
    $db             = $app['mongo.db'];
    $collection     = $app['mongo.collection.logs.name'];
    $logLevel       = \Monolog\Logger::DEBUG;

    /** @var $monolog Monolog\Logger */
    $monolog->pushHandler(new \Monolog\Handler\MongoDBHandler($client, $db, $collection, $logLevel));

    // Always Log Session ID, so we can track all user actions :-)
    $monolog->pushProcessor(function ($record) use ($app) {
        $record['extra']['session_id'] = $app['session']->getId();

        return $record;
    });

    return $monolog;
});

return $app;
