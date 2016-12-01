<?php

    use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
    use Silex\Provider\DoctrineServiceProvider;
    
    $app->register(new DoctrineServiceProvider, array(
        'db.options' => array(
            // use as an alternative environment config
            // 'driver' => 'pdo_sqlite',
            // 'path' => '/path/to/sqlite.db',
            'driver' => $app['DB_DRIVER'],
            'host' => $app['DB_HOST'],
            'dbname' => $app['DB_NAME'],
            'user' => $app['DB_USER'],
            'password' => $app['DB_PWD'],
            'charset' => 'utf8mb4',
        ),
    ));

    // to use different mapping type use

    $app->register(new DoctrineOrmServiceProvider, array(
        'orm.proxies_dir' => $app['ROOT_DIR'].'/app/data/entities/proxies',
        'orm.em.options' => array(
            'mappings' => array(
                array(
                    'type' => 'annotation',
                    'namespace' => 'App\Data\Entities',
                    'path' => $app['ROOT_DIR'].'/app/data/models',
                ),
            ),
        ),
    ));

?>