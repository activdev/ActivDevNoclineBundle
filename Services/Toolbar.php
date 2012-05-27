<?php

/*
 * This file is part of Nocline Bundle.
 *
 * (c) 2012 Bruno ABENA < bruno@activdev.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActivDev\NoclineBundle\Services;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Bundle\TwigBundle\TwigEngine;


class Toolbar 
{
    protected $templating;
    
    public function __construct(TwigEngine $templating)
    {
        $this->templating = $templating;
    }
    
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $button_content = $this->templating->render('ActivDevNoclineBundle:Default:button.html.twig');
        $response       = $event->getResponse();
        
        $response->setContent(str_replace('</body>', $button_content.'</body>', $response->getContent()));        
    }
    

}

