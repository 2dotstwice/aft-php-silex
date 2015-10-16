<?php

use \Silex\Application;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

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
        $entries = json_decode($contents);
    }

    $entries[] = $entry;
    $json = json_encode($entries);

    file_put_contents($filepath, $json);
}

/**
 * Handling requests - Basic GET request.
 */
$app->get(
    '/hello',
    function (Request $request) {
        return new Response('Hello world!');
    }
);

/**
 * Handling requests - Variable path arguments.
 */
$app->get(
    '/hello/{name}',
    function (Request $request, $name) {
        return new Response('Hello ' . $name . '!');
    }
);

/**
 * Handling requests - HTTP status codes.
 */
$app->get(
    '/blog/{postId}',
    function (Request $request, $postId) {
        $posts = [
            1 => 'Just another Silex blog.',
            2 => 'My thoughts on PHP.',
            5 => 'AFT workshop review.',
        ];

        if (!isset($posts[$postId])) {
            return new Response('Not found.', 404);
        } else {
            return new Response($posts[$postId], 200);
        }
    }
);

/**
 * Handling requests - Query parameters.
 */
$app->get(
    '/search',
    function (Request $request) {
        $filter = $request->query->get('name');
        if (empty($filter)) {
            throw new Exception('Please provide a name to filter on.');
        }

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

/**
 * Handling requests - Posting data.
 */
$app->post(
    '/contact',
    function (Request $request) {
        $filename = __DIR__ . '/files/contact-' . time() . '.txt';
        $content = $request->getContent();

        file_put_contents($filename, $content);

        return new Response($filename);
    }
);

/**
 * Middlewares - Altering responses.
 */
$app->after(
    function (Request $request, Response $response) {
        $response->headers->set('X-Generated-By', 'Silex');
    }
);

/**
 * Handling errors.
 */
$app->error(
    function (Exception $e) {
        if ($e->getCode() > 0) {
            $status = $e->getCode();
        } else {
            // Status 400 = Bad Request.
            $status = 400;
        }

        return new Response($e->getMessage(), $status);
    }
);

/**
 * Twig templates
 */
$app->get(
    'blog',
    function(Request $request, Application $app) {
        $posts = [
            1 => 'Just another Silex blog.',
            2 => 'My thoughts on PHP.',
            5 => 'AFT workshop review.',
        ];

        $html = $app['twig']->render(
            'blog.twig',
            [
                'title' => 'My awesome blog.',
                'posts' => $posts,
            ]
        );

        return new Response($html);
    }
);

/**
 * Session data - Storing.
 */
$app->register(new Silex\Provider\SessionServiceProvider());

$app->post(
    '/login',
    function (Request $request, Application $app) {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if ($username == 'john.doe' && $password == 'secret') {
            $app['session']->set('username', $username);
            return new Response('Logged in.', 200);
        } else {
            return new Response('Access denied.', 403);
        }
    }
);

/**
 * Session data - Retrieving.
 */
$app->get(
    '/user',
    function (Request $request, Application $app) {
        $username = $app['session']->get('username');

        if (!empty($username)) {
            return new Response('Logged in as ' . $username);
        } else {
            return new Response('Not logged in.');
        }
    }
);

/**
 * Filesystem - Saving JSON data (Example function).
 */
function saveJsonDataExample() {
    $users = [
        'john.doe' => [
            'password' => '662azd',
            'bio' => '...',
        ],
        'an0n' => [
            'password' => 'aazf959',
            'bio' => '...',
        ],
    ];

    $json = json_encode($users);
    file_put_contents(__DIR__ . '/files/users.json', $json);
}

/**
 * Filesystem - Reading JSON data (Example function).
 */
function readJsonDataExample() {
    $json = file_get_contents('users.json');
    $users = json_decode($json);

    // var_dump() prints the contents and
    // debug information of a variable.
    var_dump($users);
}

$app->run();
