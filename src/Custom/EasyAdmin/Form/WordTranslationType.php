<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 24.12.2017
 * Time: 20:42
 */

namespace Custom\EasyAdmin\Form;


use AppBundle\Entity\WordAttribute;
use AppBundle\Entity\WordTranslation;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Translation;
use Symfony\Component\Form\Extension\Core\Type;

class WordTranslationType extends AbstractType {

    private $doctrine;
    public function __construct(Registry $doctrine){
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('text', Type\TextType::class);
        $builder->add('auto', Type\CheckboxType::class);

        $doctrine = $this->doctrine;
        $builder->addModelTransformer(new CallbackTransformer(
            function (WordTranslation $translation=null){
                if(is_null($translation)) $translation = new WordTranslation();
                return array(
                    'text' => $translation->getText()
                );
            },
            function (array $normData) use ($doctrine){
                $text = $normData['text'];
                $auto = $normData['auto'];
                $entity = null;
                if($auto){
                    $entity = new WordTranslation();
                    $entity->setStatus(WordAttribute::STATUS_AUTO);
                }
                if(!$entity && $text) {
                    $entity = $doctrine->getRepository('AppBundle:WordTranslation')->findOneByText($text);
                }
                if(!$entity) {
                    $entity= $text ? new WordTranslation($text) : null;
                }
                return $entity;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                //'data_class' => WordTranslation::class
            )
        );
    }
}