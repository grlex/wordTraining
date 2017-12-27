<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 24.12.2017
 * Time: 20:42
 */

namespace Custom\EasyAdmin\Form;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Example;
use Symfony\Component\Form\Extension\Core\Type;

class ExampleType extends AbstractType{

    private $doctrine;
    public function __construct(Registry $doctrine){
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', Type\HiddenType::class);
        $builder->add('english', Type\TextType::class);
        $builder->add('russian', Type\TextType::class);

        $doctrine = $this->doctrine;
        $builder->addModelTransformer(new CallbackTransformer(
            function (Example $example=null){
                if(is_null($example)) $example = new Example();
                return array(
                    'id' => $example->getId(),
                    'english' => $example->getEnglish(),
                    'russian' => $example->getRussian()
                );
            },
            function (array $normData) use ($doctrine){
                $id = $normData['id'];
                $example = null;
                if($id) $example = $doctrine->getRepository('AppBundle:Example')->find($id);
                if(!$example){
                    $example= new Example();
                }
                $example->setEnglish($normData['english']);
                $example->setRussian($normData['russian']);

                return $example;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                //'data_class' => Example::class
            )
        );
    }
}