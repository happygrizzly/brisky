<?php 

    use Silex\Provider\FormServiceProvider;

    $app->register(new FormServiceProvider());
    
    use Symfony\Component\Form\FormFactoryInterface;
    use App\Forms\DocumentEditFormType;
    use App\Forms\TagFormType;

    $app->extend('form.factory', function(FormFactoryInterface $factory) {
        $factory->addType(new DocumentEditFormType());
        $factory->addType(new TagFormType());
        return $factory;
    });

?>