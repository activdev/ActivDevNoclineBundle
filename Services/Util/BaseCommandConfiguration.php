<?php

/*
 * This file is part of Nocline Bundle.
 *
 * (c) 2012 Bruno ABENA < bruno@activdev.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActivDev\NoclineBundle\Services\Util;

use Symfony\Component\HttpKernel\Kernel;

abstract class BaseCommandConfiguration
{
    protected $kernel;
    
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
    
    function getListOfConfigFormat()
    {
        return array
        (
            'annotation' => 'annotation',
            'yml'        => 'yml',
            'php'        => 'php',
            'xml'        => 'xml',
        );
    }

    function getListOfBundles()
    {
        $bundles = array();
        $srcDir  = realpath($this->kernel->getRootDir().'/../src');
        
        foreach($this->kernel->getBundles() as $k => $v)
        {
            if(preg_match("#^$srcDir#", $v->getPath()))
            {
                $bundles[$k] = $k;
            }
        }
        
        return $bundles;
    }

}