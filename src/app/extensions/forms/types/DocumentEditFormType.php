<?php 

    namespace App\Extensions\Forms;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\Extension\Core\Type\CollectionType;

    class DocumentEditFormType extends AbstractType {

        public function buildForm(FormBuilderInterface $builder, array $options) {
            
            $tags_options = array("required" => true, "entry_type" => TagForm::class);

            $builder
                ->add("title", TextType::class, array("required" => true))
                ->add("comment", TextType::class, array("required" => true))
                ->add("date", DateType::class, array("required" => true))
                ->add("file", FileType::class, array("required" => true))
                ->add("tags", CollectionType::class, $tags_options)
                ->add("directory_id", TextType::class, array("required" => true));
                
        }

        public function getName() {
            return 'document';
        }

    }

?>