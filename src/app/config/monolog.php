<?php

    $app->register(new Silex\Provider\MonologServiceProvider(), array(
        'monolog.logfile' => $app['APP_ROOT_DIR'].'/development.log',
    ));

?>