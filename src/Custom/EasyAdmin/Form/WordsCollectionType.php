<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 12.12.2017
 * Time: 21:59
 */

namespace Custom\EasyAdmin\Form;


use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WordsCollectionType extends AbstractType {

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['attr']['class'] = 'words-collection';
    }

    public function configureOptions(OptionsResolver $resolver){
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'entry_type' => \Custom\EasyAdmin\Form\WordType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false
        ));
    }

    public function getParent()
    {
        return CollectionType::class;
    }

    public function getBlockPrefix()
    {
        return 'words_collection';
    }
}