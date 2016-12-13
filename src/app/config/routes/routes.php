<?php

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\ParameterBag;
    use Symfony\Component\HttpFoundation\Response;

    // controllers

    use App\Routing\DocumentsApiControllerProvider;

    // hooks

    $app->before(function(Request $request) {

        // enable automatic redirect on session expiration

        if(!$request->getSession()->get('username')) {

            // reference: http://stackoverflow.com/a/22681873/532675

            if($request->isXmlHttpRequest()) {
                // return 401/HTTP_UNAUTHORIZED response
                $response = new Response();
                $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
                $response->headers->set('Reason', 'SESSION_EXPIRED');
                $response->headers->set('WWW-Authenticate', 'MyAuthScheme realm="app:login"');
                return $response;
            }

            return new RedirectResponse('login', 301);
        }

        // enable automatic json body decoding

        if(0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            // it is possible to inject a viewmodel mapper here
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }

    });

    // basic routes

    // GET: homepage

    $app->get('/', function() use($app) {
        return $app['twig']->render('homepage/homepage.index.twig');
    })->bind('homepage');

    // GET: login

    $app->get('/login', function(Request $request) use($app) {

        $login_data = array(
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        );

        return $app['twig']->render('login.twig', $login_data);
        
    })->bind('login');

    // areas

    $app->mount('/api/v1.0', function($api) use($app) {
        // documents API
        $api->mount('/documents', new DocumentsApiControllerProvider());
    });

?>