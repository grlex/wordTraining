<?php
/**
 * Created by PhpStorm.
 * User: Alexey Grigoriev
 * Date: 28.12.2017
 * Time: 13:55
 */

namespace AppBundle\Form;


use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;


class FilterFormHandler {
    private $formFactory;
    public function __construct(FormFactory $formFactory){
        $this->formFactory = $formFactory;
    }
    public function createForm($data = null, array $options = []){

        return $this->formFactory->createNamed('', FilterType::class, $data, $options);

    }
    public function handleRequest(Form $filterForm, Request $request){
        $fieldFieldName = $filterForm->getConfig()->getOption('field_field_name');
        $valueFieldName = $filterForm->getConfig()->getOption('value_field_name');

        $field = $request->query->get($fieldFieldName);
        $value = $request->query->get($valueFieldName);
        if($field) $filterForm->get($fieldFieldName)->submit($field);
        if($value) $filterForm->get($valueFieldName)->submit($value);

    }
} 