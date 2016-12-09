<?php

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\ParameterBag;

    // enable automatic json body decoding
    
    $app->before(function(Request $request) {
        if(0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            // it is possible to inject a viewmodel mapper here
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
    });

    // GET: homepage

    $app->get('/', function() use($app) {
        return $app['twig']->render('homepage/homepage.index.twig');
    })->bind('homepage');

    // GET: login

    $app->get('/login', function(Request $request) use($app) {
        return $app['twig']->render('login.twig', array(
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    })->bind('login');

    $app->get('/api/v1.0/documents', 'DocumentsApiController:getPage');

    /*
    
    // Areas

    $app->mount('/api/v1.0', function($api) use($app) {
        $api->mount('/documents', include 'documents.php');
    });
    
    */

?>