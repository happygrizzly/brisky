<?php

    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver' => 'pdo_mysql',
            'host' => $app['DB_HOST'],
            'dbname' => $app['DB_NAME'],
            'user' => $app['DB_USER'],
            'password' => $app['DB_PWD'],
            'charset' => 'utf8mb4',
        ),
    ));

?>