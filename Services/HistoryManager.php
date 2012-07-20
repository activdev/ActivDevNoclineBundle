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

use Symfony\Component\HttpKernel\Kernel;

class HistoryManager
{
    protected $cacheDir,
              $kernel;
    
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->setCacheDir(null, true);
    }    
    
    public function setCacheDir($dir = null, $forceCreateDir = false)
    {
        $dir = $dir ?: $this->kernel->getContainer()->getParameter('kernel.cache_dir').'/nocline';
        
        if($forceCreateDir && !is_dir($dir))
        {
            mkdir($dir, 0777, true);
        }
        
        $this->cacheDir = $dir;
    } 
    
    protected function getCommandCachedFile($commandNamespace, $commandTitle)
    {
        if($commandNamespace == 'list')
        {
            $file = $this->cacheDir . '/list.php';
            if (!is_file($file))
            {
                file_put_contents($file, '<?php return array();');
            }
            
            return $file;
        }
        
        return $this->cacheDir .'/'. strtolower(str_replace(array(':', '-'), '_', $commandNamespace.'_'.$commandTitle)).'.php';
    }
    
    public function saveCommandParamters(array $command, array $definition)
    {
        $commandNamespace = $command['commandNamespace'];
        $commandTitle     = $command['commandTitle'];
        
        unset($command['_token']);
        
        $this->saveCommandList($command, $definition);
        
        try{
            file_put_contents($this->getCommandCachedFile($commandNamespace, $commandTitle), 
                    $this->normalizeSaveArray("<?php\n\nreturn ".var_export($command, true).";"));
        }
        catch(Exception $e)
        {
            throw new Exception('Cannot write Nocline cache folder.');
        }
    }    
    
    public function getCommandParamters($commandNamespace, $commandTitle)
    {
        $file = $this->getCommandCachedFile($commandNamespace, $commandTitle);
        
        if(file_exists($file))
        {
            return require $file;
        }
        
        return null;
    }    
    
    public function getSavedCommandList($reverse = false)
    {
        if($reverse)
        {
            $a = require $this->getCommandCachedFile('list', '');
            
            return array('__list_history__' => array_reverse($a['__list_history__'], true));
        }
        
        return require $this->getCommandCachedFile('list', '');
    }
    
    protected function saveCommandList(array $command, array $definition)
    {
        unset($definition['args'], $definition['opts']);
        
        $cachedFile = $this->getCommandCachedFile('list', '');
        $listArray  = require $cachedFile;
        
        $keyLevel1 = '__list_history__';
        $keyLevel2 = $command['commandNamespace'].'-'.$command['commandTitle'];
        
        if(!isset($listArray[$keyLevel1]))
        {
            $listArray[$keyLevel1] = array();
        }
        if(!isset($listArray[$keyLevel1][$keyLevel2]))
        {
            $listArray[$keyLevel1][$keyLevel2] = array();
        }
        
        $listArray[$keyLevel1][$keyLevel2] = $definition;
        $listArray[$keyLevel1][$keyLevel2]['displayed_title'] = $command['commandNamespace'].' '.$command['commandTitle'];
        $listArray[$keyLevel1][$keyLevel2]['namespace']       = $command['commandNamespace'];
        $listArray[$keyLevel1][$keyLevel2]['cmd_title']       = $command['commandTitle'];
        
        try{
            file_put_contents($cachedFile, 
                  $this->normalizeSaveArray("<?php\n\nreturn ".var_export($listArray, true).";"));
        }
        catch(Exception $e){
            throw new Exception('Cannot write Nocline cache folder.');
        }
    }
    
    public function normalizeSaveArray($arrayStr)
    {
        return str_replace(array("'1'"), array('true'), $arrayStr);
    }    
}
