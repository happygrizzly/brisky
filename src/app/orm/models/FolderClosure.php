<?php

    namespace App\Orm\Models;

    use Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure;
    use Doctrine\ORM\Mapping as ORM;

    /**
    * @ORM\Entity
    */
    class FolderClosure extends AbstractClosure { }

?>