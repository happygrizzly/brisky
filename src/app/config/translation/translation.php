<?php 

    $app->register(new Silex\Provider\TranslationServiceProvider(), array(
        'translator.domains' => [],
    ));

?>