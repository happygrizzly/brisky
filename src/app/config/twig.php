<?php

    // register twig layout engine

    $app->register(new Silex\Provider\TwigServiceProvider(), array(
        'twig.path' => __DIR__.'/../../web/templates',
        'twig.class_path' => __DIR__.'/../../vendor/twig/lib',
        'twig.autoescape' => true
    ));

    $app->before(function() use($app) {

        // do we really need this? You can simply use 'base.twig'

        $app['twig']->addGlobal('base', null);
        $app['twig']->addGlobal('base', $app['twig']->loadTemplate('base.twig'));
        $app['twig']->addGlobal('API_PREFIX', $app['API_PREFIX']);

    });

    // register HttpFragmentServiceProvider to use partials

    $app->register(new Silex\Provider\HttpFragmentServiceProvider());

?>