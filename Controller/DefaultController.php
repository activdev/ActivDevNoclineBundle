<?php

/*
 * This file is part of Nocline Bundle.
 *
 * (c) 2012 Bruno ABENA < bruno@activdev.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActivDev\NoclineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Validator\Exception\ValidatorException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ActivDev\NoclineBundle\Services\HistoryManager;

class DefaultController extends Controller
{
    protected $commandDefinition;

    /**
     * @Route("/")
     * @Template
     */
    public function indexAction()
    {
        return array ('list' => $this->get('nocline.command_util')->getListAsArray());
    }
    
    /**
     * @Route("/form/{commandNamespace}/{commandTitle}")
     */
    public function formAction($commandNamespace, $commandTitle)
    {
        $content = $this->get('templating')->render('ActivDevNoclineBundle:Default:form.html.twig', array
        (
            'form' => $this->getCommandForm($commandNamespace, $commandTitle)->createView()
        ));

        return new Response(json_encode(array
        (
            'html' => $content . $this->get('nocline.command_configuration')->getJavascript($this->commandDefinition['command'])
        )),200);
    }
    
    /**
     * @Route("/exec")
     */
    public function execAction(Request $request)
    {
        $command = $request->request->get('command');
        $form    = $this->getCommandForm($command['commandNamespace'], $command['commandTitle']);

        $this->get('nocline.command_history')->saveCommandParamters($command);
        
        $form->bindRequest($request);
        if ($form->isValid()) 
        {
            return new Response($this->get('nocline.command_util')->run($this->commandDefinition, $command), 200);
        }

        throw new ValidatorException('The command form is not valid !');
    }
    
    protected function getCommandForm($commandNamespace, $commandTitle)
    {
        $this->commandDefinition = $this->get('nocline.command_util')->getDefinition($commandNamespace, $commandTitle);
        $commandType = $this->get('nocline.command_type');
        $commandType->setDefinition
        (
            $commandNamespace, 
            $commandTitle, 
            $this->commandDefinition
        );

        return $this->createForm($commandType, 
               $this->get('nocline.command_history')->getCommandParamters($commandNamespace, $commandTitle));        
    }
}
