<?php

    require_once __DIR__.'/../vendor/autoload.php';

    set_time_limit(0);

    $input = new ArgvInput();

    $env = $input->getParameterOption(array('--env', '-e'), getenv('ENV') ?: 'development');

    $app = require __DIR__."/../src/app.php";
    
    // the environment config part is important
    // require __DIR__."/../config/$env.php";

    $console = require __DIR__."/../src/console.php";

    $console->run();