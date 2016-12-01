<?php

    require_once __DIR__.'/../vendor/autoload.php';

    $app = new Silex\Application();
    
    $app['debug'] = getenv('DEBUG_MODE') !== false;

    $app['DB_DRIVER'] = getenv('DB_DRIVER');
    $app['DB_HOST'] = getenv('DB_HOST');
    $app['DB_NAME'] = getenv('DB_NAME');
    $app['DB_USER'] = getenv('DB_USER');
    $app['DB_PWD'] = getenv('DB_PWD');
    
    $app['ROOT_DIR'] = __DIR__.'/..';
    $app['UPLOADS_DIR'] = __DIR__.'/uploads';

    $app['DOCUMENTS_PER_PAGE'] = 5;
    $app['API_PREFIX'] = '/api/v1.0';

    $config_path = __DIR__.'/../app/config/';

    require_once $config_path.'monolog.php';
    require_once $config_path.'twig.php';
    require_once $config_path.'dumper.php';
    require_once $config_path.'database/database.php';
    require_once $config_path.'utils.php';
    require_once $config_path.'security.php';
    require_once $config_path.'validation/validation.php';
    require_once $config_path.'translation/translation.php';
    require_once $config_path.'form/form.php';
    require_once $config_path.'routes/routes.php';

    $app->run();

?>