<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 24.12.2017
 * Time: 20:42
 */

namespace Custom\EasyAdmin\Form;


use AppBundle\Entity\WordPronounce;
use AppBundle\Entity\WordSpelling;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

class WordSpellingType extends AbstractType {

    private $doctrine;
    public function __construct(Registry $doctrine){
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('text', Type\TextType::class);

        $doctrine = $this->doctrine;
        $builder->addModelTransformer(new CallbackTransformer(
            function (WordSpelling $spelling=null){
                if(is_null($spelling)) $spelling = new WordSpelling();
                return array(
                    'text' => $spelling->getText()
                );
            },
            function (array $normData) use ($doctrine){
                $text = $normData['text'];
                if(!$text) {
                    return null;
                }

                $entity = $doctrine->getRepository('AppBundle:WordSpelling')->findOneByText($text);
                if(is_null($entity)) {
                    $entity = new WordSpelling($text);
                }
                return $entity;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                //'data_class' => WordSpelling::class
            )
        );
    }
}