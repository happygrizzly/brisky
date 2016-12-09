<?php

    require_once __DIR__.'/../vendor/autoload.php';

    use Symfony\Component\Console\Application as ConsoleApplication;
    use Symfony\Component\Console\Input\ArgvInput;
    use Symfony\Component\Console\Helper\HelperSet;
    use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
    use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
    use Doctrine\ORM\Tools\Console\ConsoleRunner;

    set_time_limit(0);

    // get environment info
    $input = new ArgvInput();
    $env = $input->getParameterOption(array('--env', '-e'), getenv('ENV') ?: 'development');

    // build the application instance
    $app = require __DIR__."/../src/app.php";
    require __DIR__."/../config/$env.php";
    
    // configure the console application
    $console = new ConsoleApplication('Brisky Console Tools', '1.0');

    // register custom commands

    // add doctrine commands 
    $helperSet = new HelperSet(array(
        'db' => new ConnectionHelper($app['orm.em']->getConnection()),
        'em' => new EntityManagerHelper($app['orm.em'])
    ));
    
    $console->setHelperSet($helperSet);
    ConsoleRunner::addCommands($console);

    $console->run();

?>