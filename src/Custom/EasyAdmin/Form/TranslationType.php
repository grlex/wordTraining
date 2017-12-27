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
use AppBundle\Entity\Translation;
use Symfony\Component\Form\Extension\Core\Type;

class TranslationType extends AbstractType {

    private $doctrine;
    public function __construct(Registry $doctrine){
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', Type\HiddenType::class);
        $builder->add('meaning', Type\TextType::class);

        $doctrine = $this->doctrine;
        $builder->addModelTransformer(new CallbackTransformer(
            function (Translation $translation=null){
                if(is_null($translation)) $translation = new Translation();
                return array(
                    'id' => $translation->getId(),
                    'meaning' => $translation->getMeaning()
                );
            },
            function (array $normData) use ($doctrine){
                $id = $normData['id'];
                $translation = null;
                if($id) $translation = $doctrine->getRepository('AppBundle:Translation')->find($id);
                if(!$translation){
                    $translation= new Translation();
                }
                $translation->setMeaning($normData['meaning']);
                return $translation;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                //'data_class' => Translation::class
            )
        );
    }
}