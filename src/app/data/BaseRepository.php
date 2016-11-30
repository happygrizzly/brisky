<?php

    namespace App\Data;

    abstract class BaseRepository {

        protected $db;

        public function __construct($db) {
            $this->db = $db;
        }

    } 

?>