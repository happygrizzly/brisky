<?php

    namespace App\Routing;

    use Silex\Application;
    use Silex\ControllerProviderInterface;

    use App\Controllers\DocumentsApiController;

    class DocumentsApiControllerProvider implements ControllerProviderInterface {

        public function connect(Application $app) {

            // create route collection
            $controller = $app['controllers_factory'];

            // bind the controller's actions to routes
            $controller->get('/folders/{folder_id}/content/pages/{page}', 'DocumentsApiController:getPage');

        }

    }

?>