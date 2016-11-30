<?php 

    namespace App\Data;

    class DocumentRepository extends BaseRepository implements DocumentRepositoryInterface {

        public function __construct($db) {
            parent::__construct($db);
        }

        public function getAll() {
            
        }        

    }

?>