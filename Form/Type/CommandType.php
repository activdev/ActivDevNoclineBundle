<?php

/*
 * This file is part of Nocline Bundle.
 *
 * (c) 2012 Bruno ABENA < bruno@activdev.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActivDev\NoclineBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\CallbackValidator;
use ActivDev\NoclineBundle\Services\CommandConfiguration;

class CommandType extends AbstractType
{
    protected $definition,
              $commandTitle,
              $requiredFields,
              $commandNamespace,
              $commandConfiguration;
    
    function __construct(CommandConfiguration $commandConfiguration)
    {
        $this->commandConfiguration = $commandConfiguration;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $this->requiredFields = array();
        $this->buildFields($builder, $this->definition['args']);
        $this->buildFields($builder, $this->definition['opts']);
    }
    
    protected function buildFields(FormBuilder $builder, array $params)
    {
        foreach($params as $definition)
        {
            $field = $this->getFieldTypeAndOptions($definition);            
            $builder->add($definition['name'], $field['type'], $field['options']);
            
//            if($definition['validation']['required'])
//            {
//                $this->requiredFields[] = $definition['name'];
//            }
        }
        
        $this->setValidations($builder);
        
        $builder->add('commandNamespace', 'hidden', array('data' => $this->commandNamespace));
        $builder->add('commandTitle',     'hidden', array('data' => $this->commandTitle));
    }

    public function getFieldTypeAndOptions($definition)
    {
        //TODO remove this behaviour
        //strip the "--" in the name because of the names of options start with, 
        //for use with the configuration mapping.
        //As a consequence, if a command defines the same name for an argument and an option were are in trouble !
        $strippedName    = str_replace('--', '', $definition['name']);
        $data            = $this->commandConfiguration->getArgOptData($this->definition['command'], $strippedName);
        $isFieldRequired = $this->commandConfiguration->isArgOptRequired($this->definition['command'], 
                                    $strippedName, $definition['validation']['required']);
        
        // for server side validation 
        if($isFieldRequired)
        {
            $this->requiredFields[] = $definition['name'];
        }

        $field = array 
        (
            'options' => array 
             (
                'attr'     => array('title' => $definition['help']), 
                'label'    => $definition['label'], 
                'required' => $isFieldRequired, // for html validation
            ), 
            'type'    => $definition['type'],
        );
        
       
        if($definition['value'])
        {
            $field['options']['data'] = $definition['value'];
        }
        if($data)
        {
            $field['options']['data'] = $data;
        }

        if(is_array($data))
        {
            unset($field['options']['data']);

            $field['type'] = 'choice';

            $field['options']['choices']  = $data;
            $field['options']['expanded'] = false;
            $field['options']['multiple'] = false;
        }

        return $field;
    }
    
    public function getName()
    {
        return 'command';
    }
    
    protected function setValidations(FormBuilder &$builder)
    { 
        //print_r($this->requiredFields);
        $requiredFields = $this->requiredFields;
        $builder->addValidator(new CallbackValidator(function(\Symfony\Component\Form\FormInterface $form) use($requiredFields) 
        {
            
            foreach($requiredFields as $field)
            {
                $data = $form->get($field)->getData();
                if(is_array($data))
                {
                    //only the first value is required
                    if (!trim($data[0])) 
                    {
                        $form->get($field)->addError(new \Symfony\Component\Form\FormError("The field is required."));
                    }
                }
                elseif (!trim($data)) 
                {
                    $form->get($field)->addError(new \Symfony\Component\Form\FormError("The field is required."));
                }
            }
        }));
    }
    
    public function setDefinition($commandNamespace, $commandTitle, array $definition)
    {
        $this->definition       = $definition;
        $this->commandNamespace = $commandNamespace;
        $this->commandTitle     = $commandTitle;
    }
}