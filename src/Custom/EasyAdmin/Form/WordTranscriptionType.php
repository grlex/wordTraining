<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 24.12.2017
 * Time: 20:42
 */

namespace Custom\EasyAdmin\Form;


use AppBundle\Entity\WordAttribute;
use AppBundle\Entity\WordTranscription;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

class WordTranscriptionType extends AbstractType {

    private $doctrine;
    public function __construct(Registry $doctrine){
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', Type\HiddenType::class);
        $builder->add('text', Type\TextType::class);
        $builder->add('auto', Type\CheckboxType::class);

        $doctrine = $this->doctrine;
        $builder->addModelTransformer(new CallbackTransformer(
            function (WordTranscription $transcription=null){
                if(is_null($transcription)) $transcription = new WordTranscription();
                return array(
                    'id' => $transcription->getId(),
                    'text' => $transcription->getText()
                );
            },
            function (array $normData) use ($doctrine){
                $text = $normData['text'];
                $auto = $normData['auto'];
                $entity = null;
                if($auto){
                    $entity = new WordTranscription();
                    $entity->setStatus(WordAttribute::STATUS_AUTO);
                }
                if(!$entity && $text) {
                    $entity = $doctrine->getRepository('AppBundle:WordTranscription')->findOneByText($text);
                }
                if(!$entity) {
                    $entity= $text ? new WordTranscription($text) : null;
                }
                return $entity;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                //'data_class' => Transcription::class
            )
        );
    }
}