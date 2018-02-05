<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 27.01.2018
 * Time: 13:37
 */

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;

class PackageType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', Type\TextType::class, array(
            'required' => true,
            'label' => 'package.name',
        ));
        $builder->add('is_local', Type\CheckboxType::class, array('required' => false, 'label' => 'package.is_local'));
        $builder->add('dictionary_id', Type\HiddenType::class, array( 'required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'=>false,
        ));
    }

} 