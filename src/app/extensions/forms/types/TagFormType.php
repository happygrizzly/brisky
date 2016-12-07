<?php 

    namespace App\Extensions\Forms;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\Extension\Core\Type\TextType;

    class TagFormType extends AbstractType {

        public function buildForm(FormBuilderInterface $builder, array $options) {
            $builder->add("name", TextType::class, array("required" => true));
        }

        public function getName() {
            return 'tag';
        }

    }

?>