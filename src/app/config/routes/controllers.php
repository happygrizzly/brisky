<?php

    use App\Controllers\DocumentsApiController;

    $app['documents.controller'] = function() use($app) {
        return new DocumentsApiController();
    }

?>