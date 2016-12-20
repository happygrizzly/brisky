<?php

    new Silex\Provider\ServiceControllerServiceProvider;

    use Silex\Provider\SessionServiceProvider;
    use Silex\Provider\SecurityServiceProvider;
    
    use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
    use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

    use Silex\Provider\MonologServiceProvider;
    use Silex\Provider\TranslationServiceProvider;
    use Silex\Provider\TwigServiceProvider;
    use Silex\Provider\HttpFragmentServiceProvider;
    use Silex\Provider\VarDumperServiceProvider;
    use Silex\Provider\FormServiceProvider;
    use Symfony\Component\Form\FormFactoryInterface;

    use App\Extensions\Forms\DocumentEditFormType;
    use App\Extensions\Forms\TagFormType;

    use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
    use Silex\Provider\DoctrineServiceProvider;

    // controllers

    $app->register(new ServiceControllerServiceProvider());

    // sessions

    $app->register(new SessionServiceProvider(), array(
        'session.storage.options' => array(
            'cookie_lifetime' => 3600
        ) 
    ));

    // security

    $app->register(new SecurityServiceProvider(), array(
        'security.firewalls' => array(
            'login' => array(
                'pattern' => '^/login$',
            ),
            'secured' => array(
                'pattern' => '^.*$',
                'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
                'logout' => array('logout_path' => '/logout', 'invalidate_session' => true),
                'users' => array(
                    'admin' => array('ROLE_ADMIN', '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a'),
                    'user' => array('ROLE_USER', '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a')
                ),
            ),
        )
    ));

    $app['security.role_hierarchy'] = array(
        'ROLE_ADMIN' => ['ROLE_USER', 'ROLE_ALLOWED_TO_SWITCH'],
    );
    
    $app['security.access_rules'] = array(
        array('^/admin', 'ROLE_ADMIN'),
        array('^.*$', ['ROLE_USER']),
    );

    /*
    $app['security.default_encoder'] = function($app) {
        return new PlaintextPasswordEncoder();
    };
    */

    $app['security.utils'] = function($app) {
        return new AuthenticationUtils($app['request_stack']);
    };

    // loggging

    $app->register(new MonologServiceProvider(), $app['monolog.options']);

    // locales & translations

    $app->register(new TranslationServiceProvider(), array(
        'locale' => 'ru',
        'locale_fallback' => 'en',
        // 'translation.class_path' => __DIR__. '/../lib/vendor/symfony/src',
    ));

    $app['translator'] = $app->extend('translator', function($translator, $app) {
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', $app['ROOT_DIR'].'/src/app/resources/translations/locale.ru.yml', 'ru');
        return $translator;
    });

    // view engine

    $app->register(new TwigServiceProvider(), array(
        'twig.options' => array(
            'cache' => $app['var_dir'].'/cache/twig',
            'strict_variables' => true,
        ),
        'twig.path' => $app['ROOT_DIR'].'/src/app/resources/templates',
        // 'twig.form.templates' => array('bootstrap_3_horizontal_layout.html.twig'),
        'twig.class_path' => $app['ROOT_DIR'].'/vendor/twig/lib',
        'twig.autoescape' => true
    ));

    $app->register(new HttpFragmentServiceProvider());

    // extend twig ..
    $app['twig'] = $app->extend('twig', function($twig, $app) {

        // .. with the asset() function
        $twig->addFunction(new \Twig_SimpleFunction('asset', function($asset) use ($app) {
            $base = $app['request_stack']->getCurrentRequest()->getBasePath();
            return sprintf($base.'/'.$asset, ltrim($asset, '/'));
        }));

        // .. with the API prefix
        $app['twig']->addGlobal('API_PREFIX', $app['API_PREFIX']);

        return $twig;
    });

    $app->register(new VarDumperServiceProvider());

    $app->register(new ValidatorServiceProvider());
    
    $app->register(new FormServiceProvider());

    $app->extend('form.factory', function(FormFactoryInterface $factory) {
        $factory->addType(new DocumentEditFormType());
        $factory->addType(new TagFormType());
        return $factory;
    });

    $app->register(new DoctrineServiceProvider, array(
        'db.options' => array('url' => $app['DB_CONN_STRING']),
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
        'orm.proxies_dir' => $app['ROOT_DIR'].'/src/app/orm/models/proxies',
        'orm.em.options' => array(
            'mappings' => array(
                array(
                    'type' => 'annotation',
                    'namespace' => 'App\Orm\Models',
                    'path' => $app['ROOT_DIR'].'/src/app/orm/models',
                ),
            ),
        ),
    ));
    
    /*
    $app->register(new WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => $app['var_dir'].'/cache/profiler',
        'profiler.mount_prefix' => '/_profiler', // this is the default
    ));
    */

?>