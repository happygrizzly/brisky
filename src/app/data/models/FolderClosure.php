<?php

    namespace App\Data\Models;

    use Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure;
    use Doctrine\ORM\Mapping as ORM;

    /**
    * @ORM\Entity
    */
    class FolderClosure extends AbstractClosure { }

?>