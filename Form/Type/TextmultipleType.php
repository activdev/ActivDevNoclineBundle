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
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class TextmultipleType extends AbstractType
{
    
    public function getParent(array $options)
    {
        return 'text';
    }

    public function getDefaultOptions(array $options)
    {
        return array_merge($options, array('attr' => array('class' => 'nocline-text-add')));
    }
    
    public function buildView(FormView $view, FormInterface $form)
    {
        $view->set('full_name', $view->get('full_name').'[]');
    }
    
    public function getName()
    {
        return 'textmultiple';
    }
}