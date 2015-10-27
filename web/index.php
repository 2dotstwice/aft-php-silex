<?php

use \Silex\Application;
use \Symfony\Component\HttpFoundation\RedirectResponse;
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

$app->post(
    '/guestbook',
    function (Request $request, Application $app) {
        $name = $request->request->get('name');
        $message = $request->request->get('message');

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Please enter your name.';
        }

        if (empty($message)) {
            $errors[] = 'Please enter a message.';
        }

        if (!empty($errors)) {
            $html = $app['twig']->render(
                'guestbook.twig',
                [
                    'submitUrl' => '/guestbook',
                    'errors' => $errors,
                    'formValues' => [
                        'name' => $name,
                        'message' => $message,
                    ]
                ]
            );

            return new Response($html);
        }

        $post = [
            'id' => uniqid(),
            'created' => time(),
            'name' => $name,
            'message' => $message,
        ];

        saveGuestbookEntry($post);

        return new RedirectResponse('/guestbook');
    }
);

function saveGuestbookEntry($entry) {
    $filepath = __DIR__ . '/files/guestbook-entries.json';

    $entries = [];

    if (file_exists($filepath)) {
        $contents = file_get_contents($filepath);
        $entries = json_decode($contents, true);
    }

    $entries[$entry['id']] = $entry;
    $json = json_encode($entries);

    file_put_contents($filepath, $json);
}

include_once __DIR__ . '/examples.php';

$app->run();
