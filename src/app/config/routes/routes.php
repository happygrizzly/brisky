<?php

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\ParameterBag;

    // enable automatic json body decoding
    // note: this can be attached only for a set of methods
    // as a $route->before(...) call 

    $app->before(function(Request $request) {
        if(0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            // it is possible to inject a viewmodel mapper here
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
    });

    // Areas

    $app->mount('/api/v1.0', function($api) use($app) {
        $api->mount('/documents', include 'documents.php');
    });

    // GET: documents homepage

    $app->get('/documents', function() use($app) {
        return $app['twig']->render('documents/documents.index.twig');
    })->bind('documents_home');

    // GET: homepage

    $app->get('/', function() use($app) {
        return $app['twig']->render('homepage/homepage.index.twig');
    })->bind('homepage');

    // GET: Login

    $app->get('/login', function(Request $request) use($app) {
        return $app['twig']->render('login.twig', array(
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    })->bind('login');

?>