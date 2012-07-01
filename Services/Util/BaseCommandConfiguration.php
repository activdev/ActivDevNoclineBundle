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

    function getListOfConfigFormat()
    {
        return array
        (
            'yml'        => 'yml',
            'php'        => 'php',
            'xml'        => 'xml',
            'annotation' => 'annotation',
        );
    }

    function getListOfBundles()
    {
        $bundles = array();
        $srcDir = $this->getSrcDir();

        foreach($this->kernel->getBundles() as $k => $v)
        {
            if(preg_match("#^$srcDir#", $v->getPath()))
            {
                $bundles[$k] = $k;
            }
        }

        return $bundles;
    }

    function getListOfEntities()
    {
        $bundleEntities = array();
        $bundles = glob($this->getSrcDir() . '/*/*Bundle/*Bundle.php');
        
        foreach($bundles as $bundle)
        {
            $bundllName = basename($bundle, '.php');
            $entityDir  = dirname($bundle).'/Entity';

            if(!is_dir($entityDir))
            {
                continue;
            }

            foreach(glob($entityDir.'/*.php') as $entity)
            {
                $name = basename($entity, '.php');
                if(!preg_match('#Repository$#', $name))
                {
                    $bundleEntities[$bundllName.':'.$name] = $bundllName.':'.$name;
                }
            }
        }
        
        return $bundleEntities;
    }

    protected function getSrcDir()
    {
        return realpath($this->kernel->getRootDir() . '/../src');
    }

}