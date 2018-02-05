<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 27.12.2017
 * Time: 19:53
 */
namespace AppBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;

class FilterType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add($options['field_field_name'], Type\HiddenType::class,array(
        ));
        $builder->add($options['value_field_name'], Type\TextType::class, array(
            'label'=>false,
            'block_name'=>'filterValue'
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(
            'field_field_name',
            'value_field_name'
        );
        $resolver->setDefaults(array(
            'method' =>  'get',
            'csrf_protection' => false,
            'field_field_name' => 'filterField',
            'value_field_name' => 'filterValue'
        ));
    }
}