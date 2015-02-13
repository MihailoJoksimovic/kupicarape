<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

// Mongo Settings
$app['mongo.host']      = '127.0.0.1';
$app['mongo.db_name']   = 'kupicarape';

// Mailing Settings
$app['mailing.notifications']   = 'kupicarape@gmail.com';