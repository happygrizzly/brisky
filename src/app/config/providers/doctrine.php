<?php

    use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
    use Silex\Provider\DoctrineServiceProvider;
    
    // this one registers Doctrine's event manager

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

    // globally used cache driver, in production use APC or memcached
    $cache = new Doctrine\Common\Cache\ArrayCache();

    // standard annotation reader
    $annotationReader = new Doctrine\Common\Annotations\AnnotationReader();
    $cachedAnnotationReader = new Doctrine\Common\Annotations\CachedReader(
        $annotationReader, // use reader
        $cache // and a cache driver
    );

    // create a driver chain for metadata reading
    $driverChain = new Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();

    // load superclass metadata mapping only, into driver chain
    // also registers Gedmo annotations.NOTE: you can personalize it
    Gedmo\DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
        $driverChain, // our metadata driver chain, to hook into
        $cachedAnnotationReader // our cached annotation reader
    );

    // tree
    $treeListener = new Gedmo\Tree\TreeListener();
    $treeListener->setAnnotationReader($cachedAnnotationReader);
    $app['event_manager']->addEventSubscriber($treeListener);

    /*
    // blameable
    $blameableListener = new \Gedmo\Blameable\BlameableListener();
    $blameableListener->setAnnotationReader($cachedAnnotationReader);
    $blameableListener->setUserValue('MyUsername'); // determine from your environment
    $evm->addEventSubscriber($blameableListener);
    */

    // timestampable
    $timestampableListener = new Gedmo\Timestampable\TimestampableListener();
    $timestampableListener->setAnnotationReader($cachedAnnotationReader);
    $app['event_manager']->addEventSubscriber($timestampableListener);

    $app->register(new DoctrineOrmServiceProvider, array(
        'orm.proxies_dir' => $app['ROOT_DIR'].'/app/data/models/proxies',
        'orm.em.options' => array(
            'mappings' => array(
                array(
                    'type' => 'annotation',
                    'namespace' => 'App\Data\Models',
                    'path' => $app['ROOT_DIR'].'/app/data/models',
                ),
            ),
        ),
    ));

?>