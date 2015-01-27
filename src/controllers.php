<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    $socks = array(
        array(
            'id'    => 'crne_dugacke',
            'name'  => 'Crne Dugacke',
            'image' => 'black_long_socks.jpg'
        ),
        array(
            'id'    => 'crne_kratke',
            'name'  => 'Crne Kratke',
            'image' => 'black_long_socks.jpg'
        ),
        array(
            'id'    => 'bele_kratke',
            'name'  => 'Bele Kratke',
            'image' => 'black_long_socks.jpg'
        ),
        array(
            'id'    => 'bele_dugacke',
            'name'  => 'Bele Dugacke',
            'image' => 'black_long_socks.jpg'
        ),
    );

    return $app['twig']->render('index.twig', array('socks' => $socks));
})
->bind('homepage')
;

$app->post('/signup', function() use ($app) {
    return true;
})->bind('signup');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.twig',
        'errors/'.substr($code, 0, 2).'x.twig',
        'errors/'.substr($code, 0, 1).'xx.twig',
        'errors/default.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
