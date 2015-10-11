<?php

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

$app->get(
    '/hello',
    function (Request $request) {
        return new Response('Hello world!');
    }
);

$app->get(
    '/hello/{name}',
    function (Request $request, $name) {
        return new Response('Hello ' . $name . '!');
    }
);

$app->get(
    '/search',
    function (Request $request) {
        $filter = $request->query->get('name');
        $names = ['Bill Gates', 'Steve Jobs', 'Steve Wozniak'];

        $matches = [];
        foreach ($names as $name) {
            if (stripos($name, $filter) !== false) {
                $matches[] = $name;
            }
        }

        return new Response(
            count($matches) . ' result(s): ' . implode(', ', $matches)
        );
    }
);

$app->post(
    '/contact',
    function (Request $request) {
        $filename = __DIR__ . '/files/contact-' . time() . '.txt';
        $content = $request->getContent();

        file_put_contents($filename, $content);

        return new Response($content);
    }
);

$app->run();
