<?php

use \Silex\Application;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../templates',
));

$app->get(
    '/guestbook',
    function (Application $app) {
        $html = $app['twig']->render(
            'guestbook.twig',
            ['submitUrl' => '/guestbook']
        );

        return new Response($html);
    }
);

include_once __DIR__ . '/examples.php';

$app->run();
