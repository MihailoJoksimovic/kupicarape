<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function (Request $request) use ($app) {

    $app->log(
        sprintf("New visitor: '%s'", $request->getClientIp()),
        array(
            'new_visitor'   => true,
            'headers'       => $request->headers->all()
        )
    );

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

$app->post('/signup', function(Request $request) use ($app) {
    $data       = $request->request->all();
    $headers    = $request->headers->all();

    $email      = $request->get('email');
    $socksType  = $request->get('socks_type');

    $app->log(
        sprintf(
            "User '%s' submitted form; Email: '%s'; Selected socks: '%s'",
            $request->getClientIp(),
            $email,
            $socksType
        ),
        array(
            'signup'        => true,
            'form_data'     => $data,
            'headers'       => $headers
        )
    );

    /** @var $signupsCollection \MongoCollection */
    $signupsCollection = $app['mongo.collection.signups'];

    $response = true;

    try {
        $signupsCollection->insert(array(
            'headers'   => $headers,
            'data'      => $data,
            'date'      => new MongoDate()
        ));
    } catch (\Exception $e) {
        $app->log(
            sprintf(
                "Exception has occurred when user '%s' submitted data. Exception text: '%s'",
                $request->getClientIp(),
                $e->getMessage()
            ),
            array(
                'exception'             => true,
                'signup_form_exception' => true,
                'form_data'             => $data,
                'request_headers'       => $headers
            ),
            \Monolog\Logger::ALERT
        );

        $response = new Response('Exception occurred.', 401);
    }


    return $response;
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
