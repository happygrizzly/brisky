<?php

    namespace App\Controllers;

    use Silex\Application;
    use Symfony\Component\HttpFoundation\JsonResponse;

    class DocumentsApiController {
        
        private $documents = array();

        public function __construct() {

            // new \Symfony\Component\HttpFoundation\Response( $data, 200, $headers );

            for($i = 0; $i < 100; $i++) {
                $this->documents []= array(
                    "id" => $i + 1,
                    "title" => "test"
                );
            }

        }

        public function getPage($page = 1, $pageSize = 5) {
            $data = array_slice($this->documents, $page * $pageSize, $pageSize);
            return new JsonResponse($data);
        }

    }

?>