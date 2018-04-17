<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 24.12.2017
 * Time: 20:42
 */

namespace Custom\EasyAdmin\Form;


use AppBundle\Entity\WordAttribute;
use AppBundle\Entity\WordPicture;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;
use Vich\UploaderBundle\Form\Type\VichFileType;

class WordPictureType extends AbstractType {

    private $doctrine;
    public function __construct(Registry $doctrine){
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', Type\HiddenType::class, array('required'=>'false'));
        $builder->add('filename', Type\HiddenType::class, array('required'=>false));
        $builder->add('url', Type\HiddenType::class, array('required'=>'false'));
        $builder->add('title', Type\HiddenType::class, array('required'=>'false'));
        $builder->add('chosen', Type\HiddenType::class, array('required'=>'false'));

        $self = $this;
        $builder->addModelTransformer(new CallbackTransformer(
            function (WordPicture $picture = null) use ($self){ return $self->transform($picture); },
            function (array $normData) use ($self){ return $self->reverseTransform($normData); }
        ));
    }
    private function transform (WordPicture $picture=null){
        if(is_null($picture)) $picture = new WordPicture();
        return array(
            'id' => $picture->getId(),
            'filename' => $picture->getFilename(),
            'url' => $picture->getURL(),
            'title' => $picture->getTitle(),
            'chosen' => $picture->getChosen() ? 1 : 0
        );
    }
    private function reverseTransform (array $normData){
        $id = $normData['id'];
        //$filename = $normData['filename'];
        $url = $normData['url'];
        $title = $normData['title'];
        $chosen = $normData['chosen'];

        $entity = null;
        if(!is_null($id)) {
            $entity = $this->doctrine->getRepository('AppBundle:WordPicture')->find($id);
        }
        else if($url) {
            $entity = new WordPicture();
            $entity->setURL($url);
            $entity->setTitle($title);
            $entity->setStatus(WordPicture::STATUS_PICTURE_LINK);
        }
        $entity->setChosen($chosen == 1 );
        return $entity;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                //'data_class' => WordPicture::class
                'translation_domain' => 'CustomEasyAdminBundle'
            )
        );
    }
}
