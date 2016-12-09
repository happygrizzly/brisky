<?php

    require_once __DIR__.'/../../vendor/autoload.php';

    $app = new Silex\Application();

    // API

    $app['API_PREFIX'] = "/api/v1.0";
    
    // application directories

    $app['ROOT_DIR'] = __DIR__.'/../../'; 
    $app['VAR_DIR'] = $app['ROOT_DIR'].'/src/var';
    $app['UPLOADS_DIR'] = $app['ROOT_DIR'].'/uploads';

    // locale

    $app['locale'] = 'ru';
    
    $app['http_cache.cache_dir'] = function(Application $app) {
        return $app['VAR_DIR'].'/cache/http';
    };
    
    // logging options
    
    $app['monolog.options'] = [
        'monolog.logfile' => $app['VAR_DIR'].'/logs/app.log',
        'monolog.name' => 'app',
        'monolog.level' => 300, // = Logger::WARNING
    ];

    // the environment settings

    $app['ENV'] = getenv('ENV') ?: 'development';
    $app['debug'] = in_array($app['ENV'], array('development'));

    // database connection

    $db_conn_str = getenv('DB_CONN_STRING');
    if(!isset($db_conn_str)) {
        throw new \RuntimeException("Database connection string not found");
    }

    $app['DB_CONN_STRING'] = $db_conn_str;

    // providers config

    $envConfig = sprintf('%s/src/app/config/env/%s.php', $app['ROOT_DIR'], $app['ENV']);
    if(!file_exists($envConfig)) {
        throw new \RuntimeException(sprintf('The file "%s" does not exist.', $envConfig));
    }
    
    require $envConfig;

    // controllers

    $controllersConfig = $app['ROOT_DIR'].'/src/app/config/routes/controllers.php';
    require $controllersConfig;

    $routesConfig = $app['ROOT_DIR'].'/src/app/config/routes/routes.php';
    require $routesConfig;

?>