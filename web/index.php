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

$redirectIfNotLoggedIn = function (Request $request, Application $app) {
    $username = $app['session']->get('username');

    if (empty($username)) {
        $originalPath = $request->getPathInfo();
        return new RedirectResponse('/user/login?destination=' . $originalPath);
    }

    return null;
};

$app->get(
    '/guestbook',
    function (Application $app) {
        $html = $app['twig']->render(
            'guestbook.twig',
            [
                'submitUrl' => '/guestbook',
                'entries' => readGuestbookEntries()
            ]
        );

        return new Response($html);
    }
)->before($redirectIfNotLoggedIn);

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
                    'entries' => readGuestbookEntries(),
                    'errors' => $errors,
                    'formValues' => [
                        'name' => $name,
                        'message' => $message,
                    ],
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
)->before($redirectIfNotLoggedIn);

function saveGuestbookEntry($entry) {
    $filepath = __DIR__ . '/files/guestbook-entries.json';

    $entries = readGuestbookEntries();
    $entries[$entry['id']] = $entry;

    $json = json_encode($entries);

    file_put_contents($filepath, $json);
}

function readGuestbookEntries() {
    $filepath = __DIR__ . '/files/guestbook-entries.json';

    $entries = [];

    if (file_exists($filepath)) {
        $contents = file_get_contents($filepath);
        $entries = json_decode($contents, true);
    }

    return $entries;
}

$app->get(
    '/user/registration',
    function (Application $app) {
        $html = $app['twig']->render(
            'registration.twig',
            [
                'submitUrl' => '/user/registration',
                'loginUrl' => '/user/login',
            ]
        );

        return new Response($html);
    }
);

$app->post(
    '/user/registration',
    function (Request $request, Application $app) {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $passwordConfirmation = $request->request->get('passwordConfirmation');
        $name = $request->request->get('name');
        $email = $request->request->get('email');

        $errors = [];

        if (empty($username)) {
            $errors[] = 'Please enter a username.';
        }
        if (empty($name)) {
            $errors[] = 'Please enter your name.';
        }
        if (empty($email)) {
            $errors[] = 'Please enter your e-mail address.';
        }

        if (empty($password)) {
            $errors[] = 'Please enter a password.';
        } else if (empty($passwordConfirmation)) {
            $errors[] = 'Please confirm your password.';
        } else if ($password !== $passwordConfirmation) {
            $errors[] = 'The passwords you entered do not match.';
        }

        if (!empty($errors)) {
            $html = $app['twig']->render(
                'registration.twig',
                [
                    'submitUrl' => '/user/registration',
                    'loginUrl' => '/user/login',
                    'errors' => $errors,
                    'formValues' => [
                        'username' => $username,
                        'name' => $name,
                        'email' => $email,
                    ],
                ]
            );

            return new Response($html);
        }

        $user = [
            'username' => $username,
            'password' => md5($password),
            'name' => $name,
            'email' => $email,
        ];

        saveUser($user);

        return new RedirectResponse('/user/login');
    }
);

$app->get(
    '/user/login',
    function (Request $request, Application $app) {
        $destination = $request->query->get('destination');

        $html = $app['twig']->render(
            'login.twig',
            [
                'submitUrl' => '/user/login?destination=' . $destination,
                'registrationUrl' => '/user/registration'
            ]
        );

        return new Response($html);
    }
);

$app->post(
    '/user/login',
    function (Request $request, Application $app) {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $users = readUsers();

        if (isset($users[$username]) && md5($password) == $users[$username]->password) {
            $app['session']->set('username', $username);

            $destination = $request->query->get('destination');
            if (empty($destination)) {
                $destination = '/user/profile';
            }

            return new RedirectResponse($destination);
        } else {
            $html = $app['twig']->render(
                'login.twig',
                [
                    'submitUrl' => '/user/login',
                    'registrationUrl' => '/user/registration',
                    'errors' => [
                        'The username or password you entered are incorrect.'
                    ],
                    'formValues' => [
                        'username' => $username
                    ]
                ]
            );

            return new Response($html);
        }
    }
);

$app->get(
    '/user/profile',
    function (Application $app) {
        $html = $app['twig']->render(
            'profile.twig',
            [
                'username' => $app['session']->get('username'),
                'logoutUrl' => '/user/logout'
            ]
        );

        return new Response($html);
    }
)->before($redirectIfNotLoggedIn);

$app->get(
    '/user/logout',
    function (Application $app) {
        $app['session']->remove('username');
        return new RedirectResponse('/user/login');
    }
)->before($redirectIfNotLoggedIn);

function saveUser($user) {
    $filepath = __DIR__ . '/files/users.json';

    $users = readUsers();
    $users[$user['username']] = $user;

    $json = json_encode($users);

    file_put_contents($filepath, $json);
}

function readUsers() {
    $filepath = __DIR__ . '/files/users.json';

    $users = [];

    if (file_exists($filepath)) {
        $contents = file_get_contents($filepath);
        $users = json_decode($contents, true);
    }

    return $users;
}

include_once __DIR__ . '/examples.php';

$app->run();
