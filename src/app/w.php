<?php

    // see: https://github.com/lyrixx/Silex-Kitchen-Edition/blob/master/src/App/Application.php
    // add setEnvIf for the DB 

    namespace App;
    
    use Silex\Application as SilexApplication;

    use Silex\Provider\MonologServiceProvider;
    use Silex\Provider\ValidatorServiceProvider;
    use Silex\Provider\TranslationServiceProvider;
    use Silex\Provider\TwigServiceProvider;
    use Silex\Provider\HttpFragmentServiceProvider;
    use Silex\Provider\FormServiceProvider;
    use Symfony\Component\Form\FormFactoryInterface;
    use Silex\Provider\SessionServiceProvider;

    use App\Forms\DocumentEditFormType;
    use App\Forms\TagFormType;

    use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
    use Symfony\Component\Translation\Loader\YamlFileLoader;

    class Application extends SilexApplication { 

        private $rootDir;
        private $env;

        public function __construct($env) { 

            $this->rootDir = __DIR__.'/../../';
            $this->env = $env;

            parent::__construct();

            $app = $this;

            // override these values in resources/config/prod.php file

            $app['api_prefix'] = "api/v1.0"; 
            $app['var_dir'] = $this->rootDir.'/var';
            $app['locale'] = 'ru';
            
            $app['http_cache.cache_dir'] = function(Application $app) {
                return $app['var_dir'].'/cache/http';
            };
            
            $app['monolog.options'] = [
                'monolog.logfile' => $app['var_dir'].'/logs/app.log',
                'monolog.name' => 'app',
                'monolog.level' => 300, // = Logger::WARNING
            ];

            // $app['security.users'] = array('alice' => array('ROLE_USER', 'password'));

            // get environment configuration

            $configFile = sprintf('%s/config/%s.php', $this->rootDir, $env);
            if(!file_exists($configFile)) {
                throw new \RuntimeException(sprintf('The file "%s" does not exist.', $configFile));
            }
            
            require $configFile;

            // providers

            $app->register(new SessionServiceProvider());

            $app->register(new Silex\Provider\SecurityServiceProvider(), array(
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
                            'user' => array('ROLE_USER', '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a'),
                            'viewer' => array('ROLE_VIEWER', '$2y$10$3i9/lVd8UOFIJ6PAMFt8gu3/r5g0qeCJvoSlLCsvMTythye19F77a')
                        ),
                    ),
                )
            ));

            $app['security.role_hierarchy'] = array(
                'ROLE_ADMIN' => array('ROLE_USER'),
            );
            
            $app['security.access_rules'] = array(
                array('^/admin', 'ROLE_ADMIN'),
                array('^.*$', ['ROLE_USER', 'ROLE_VIEWER']),
            );

            $app['security.default_encoder'] = function($app) {
                return new PlaintextPasswordEncoder();
            };
        
            $app['security.utils'] = function($app) {
                return new AuthenticationUtils($app['request_stack']);
            };

            $app->register(new MonologServiceProvider(), $app['monolog.options']);

            $app->register(new TranslationServiceProvider());
            $app['translator'] = $app->extend('translator', function($translator, $app) {
                $translator->addLoader('yaml', new YamlFileLoader());
                $translator->addResource('yaml', $this->rootDir.'/resources/translations/ru.yml', 'ru');
                return $translator;
            });

            $app->register(new TwigServiceProvider(), array(
                'twig.options' => array(
                    'cache' => $app['var_dir'].'/cache/twig',
                    'strict_variables' => true,
                ),
                'twig.path' => $this->rootDir.'/resources/templates',
                // 'twig.form.templates' => array('bootstrap_3_horizontal_layout.html.twig'),
                'twig.class_path' => $this->rootDir.'/vendor/twig/lib',
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
                $app['twig']->addGlobal('API_PREFIX', $app['api_prefix']);

                return $twig;
            });

            // put it to the dev config
            // $app->register(new Silex\Provider\VarDumperServiceProvider());
            $app->register(new ValidatorServiceProvider());
            
            $app->register(new FormServiceProvider());
            $app->extend('form.factory', function(FormFactoryInterface $factory) {
                $factory->addType(new DocumentEditFormType());
                $factory->addType(new TagFormType());
                return $factory;
            });

            

        }

    }

?>