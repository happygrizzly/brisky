<?php

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\BinaryFileResponse;
    use Symfony\Component\HttpFoundation\ResponseHeaderBag;
    use Symfony\Component\Validator\Mapping\ClassMetadata;
    use Symfony\Component\Validator\Constraints as Assert;

    // refactor: incapsulate data requests into services 

    $documents = $app['controllers_factory'];

    class DocumentsApiController {
        
    }

    /*    
        $form->add('my_file', 'file', [
            'required' => true,
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF',
                ])
            ]
        ]);
    */

    // GET: a page of documents

    $documents->get('/directories/{directory_id}/page/{page}', function($directory_id, $page) use($app) {

        $sql_documents_count = "
            SELECT COUNT(*) rows_count 
            FROM documents 
            WHERE id_directory = $directory_id
        ";

        $documents_count = $app['db']->fetchColumn($sql_documents_count);

        $skip = ($page - 1) * $app['DOCUMENTS_PER_PAGE'];
        $take = $app['DOCUMENTS_PER_PAGE'];

        $sql_documents = "
            SELECT *                 
            FROM documents d 
            WHERE id_directory = $directory_id 
            ORDER BY title
            LIMIT $skip, $take
        ";

        $documents = $app['db']->fetchAll($sql_documents);

        $tagged_documents = [];
        foreach($documents as $document) {
            
            $document_id = $document['id'];
            $sql_tagged_documents = "
                SELECT tags.id, tags.label FROM tags 
                INNER JOIN documents_tags tagged_docs ON 
                    tagged_docs.id_tag = tags.id AND
                    tagged_docs.id_document = $document_id
            ";

            $tags = $app['db']->fetchAll($sql_tagged_documents);
            $document['tags'] = $tags;
            $tagged_documents []= $document;
        }

        $response = [
            "documentsCount" => $documents_count,
            "documents" => $tagged_documents
        ];

        return $app->json($response, Response::HTTP_OK);

    })->assert('page', '\d+')->value('page', 1)->convert('page', function($page) {
        return (int)$page;
    })->bind('documents_list');

    // GET: tags with label like $request->get('label');

    $documents->get('/tags', function(Request $request) use($app) {

        $label = $request->get('label');
        $tags = $app['db']->fetchAll("SELECT id, label FROM tags where label LIKE '%{$label}%'");

        return $app->json($tags, Response::HTTP_OK);

    })->bind('documents_tags');

    $documents->get('/nav/toc', function() use($app) {

        $toc = [];
        $departments = $app['db']->fetchAll("SELECT id, name FROM departments ORDER BY id");    

        foreach($departments as $department) {

            $department_id = $department['id'];

            // refactor: switch to repository/service code instead of this
            $toc_section_flat = $app['db']->fetchAll("CALL GET_DEPARTMENT_DIRECTORIES($department_id)");
            $toc_section_tree = $app['services.tree_parser']->parse($toc_section_flat);
            
            $toc []= array(
                "department" => array(
                    "id" => $department_id, 
                    "name" => $department['name']
                ),
                "tree" => $toc_section_tree
            );
        }

        $response = new \Symfony\Component\HttpFoundation\JsonResponse();
        $response->setEncodingOptions(JSON_NUMERIC_CHECK);
        $response->setData($toc);

        return $response;

    })->bind('documents_nav_toc');

    // refactor: breadcrumbs should be accessible 

    $documents->get('/nav/bc/directories/{directory_id}', function($directory_id) use($app) {

        $sql = "
            SELECT c.name
            FROM directories AS c
            JOIN directories_tree_paths AS t ON c.id = t.id_ancestor
            WHERE t.id_descendant = $directory_id 
            ORDER BY id_parent
        ";

        $breadcrumbs = $app['db']->fetchAll($sql);

        return $app->json($breadcrumbs, Response::HTTP_OK);

    })->bind('documents_nav_breadcrumbs');

    // POST: create new document under a given category
    // refactor: consider changing 'new' to 'save'

    $documents->post('/new', function(Request $request) use($app) {

        if(!$request->files->has('file')) {
            return $app->json(array(['file' => ['File is not found']]), Response::HTTP_BAD_REQUEST);
        }

        $errors = $app['services.validation']->validate($request->request->all(), __DIR__.'/document.yml');

        if(count($errors) > 0) {
            return $app->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $file = $request->files->get('file');
        $tags = $request->request->get('tags');

        $document = array(
            "title" => $request->request->get('title'),
            "comment" => $request->request->get('comment'),
            "document_date" => $request->request->get('date'),
            "id_directory" => $request->request->get('directory_id'),
        );

        $app['db']->beginTransaction();
        
        try {         

            // generate document's filename
            $extension = $file->guessExtension();
            $ts = (new \DateTime())->getTimestamp();
            $document['filename'] = "$ts.$extension"; 
            
            // create new document
            $app['db']->insert('documents', $document);
            $document['id'] = $app['db']->lastInsertId();

            // save document to file system
            $file->move("{$app['UPLOADS_DIR']}/documents", $document['filename']);

            // attach tags to the document
            foreach($tags as $tag) {

                if(!isset($tag['id'])) {
                    $app['db']->insert('tags', array('label' => $tag['label']));
                    $tag['id'] = $app['db']->lastInsertId();
                }
                
                // should be managed automatically?
                $app['db']->insert('documents_tags', array(
                    'id_document' => $document['id'],
                    'id_tag' => $tag['id']
                ));

            }

            // add tags to a document array
            $document['tags'] = $tags;

            $app['db']->commit();

            return $app->json($document, Response::HTTP_CREATED);

        } catch(Exception $e) {

            $app['monolog']->error($e->getMessage());

            if(isset($document['filename'])) {
                unlink("{$app['UPLOADS_DIR']}/documents/{$document['filename']}");
            }

            $app['db']->rollBack();
            
            // 2. return either a meaningful message or an empty array

            return $app->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }        
        
    })->bind('documents_new');

    $documents->post('/delete', function(Request $request) use($app) {

        // reading: http://stackoverflow.com/questions/17214008/delete-a-file-from-database-and-file-system

        $id = $request->get('id'); 

        if(!isset($id)) {
            return $app->json(['document' => ['id is required']], Response::HTTP_BAD_REQUEST);
        }

        $sql = "SELECT id, filename FROM documents WHERE id = ?";
        $document = $app['db']->fetchAssoc($sql, array($id));

        if($document == null) {
            return $app->json([], Response::HTTP_NO_CONTENT);
        }

        $app['db']->beginTransaction();

        try {

            $app['db']->delete('documents', ['id' => $id]);

            $filename = $document['filename'];
            $path = "{$app['UPLOADS_DIR']}/documents/$filename";

            if(file_exists($path)) {
                chmod($path, 0777);
                unlink($path);
            }

            $app['db']->commit();

        }
        catch(Exception $e) {
            $app['monolog']->error($e->getMessage());
            $app['db']->rollBack();
        }

        return $app->json(['id' => $id], Response::HTTP_OK);

    })->bind('documents_delete_by_id');

    return $documents;

?>