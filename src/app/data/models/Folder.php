<?php

    namespace App\Data\Models;

    use Gedmo\Mapping\Annotation as Gedmo;
    use Doctrine\ORM\Mapping as ORM;

    /**
    * @Gedmo\Tree(type="closure")
    * @Gedmo\TreeClosure(class="App\Data\Models\FolderClosure")
    * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\ClosureTreeRepository")
    */
    class Folder {

        /**
        * @Gedmo\Timestampable(on="create")
        * @ORM\Column(type="datetime")
        */
        private $created;

        /**
        * @ORM\Column(name="id", type="integer")
        * @ORM\Id
        * @ORM\GeneratedValue
        */
        private $id;

        /**
        * @ORM\Column(name="title", type="string", length=100)
        */
        private $title;

        /**
        * This parameter is optional for the closure strategy
        *
        * @ORM\Column(name="level", type="integer", nullable=true)
        * @Gedmo\TreeLevel
        */
        private $level;

        /**
        * @Gedmo\TreeParent
        * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
        * @ORM\ManyToOne(targetEntity="Folder", inversedBy="children")
        */
        private $parent;

        public function getId() {
            return $this->id;
        }

        public function setTitle($title) {
            $this->title = $title;
        }

        public function getTitle() {
            return $this->title;
        }

        public function setParent(Folder $parent = null) {
            $this->parent = $parent;
        }

        public function getParent() {
            return $this->parent;
        }

        public function addClosure(FolderClosure $closure) {
            $this->closures[] = $closure;
        }

        public function setLevel($level) {
            $this->level = $level;
        }

        public function getLevel() {
            return $this->level;
        }

        public function isRoot() {
            $this->parent == null;
        }

    }

?>