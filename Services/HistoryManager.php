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

class HistoryManager
{
    protected $cacheDir;
    
    public function __construct()
    {
        $this->setCacheDir();
    }    
    
    public function setCacheDir($dir = null)
    {
        $this->cacheDir = $dir ?: __DIR__ . '/../Cache';
    } 
    
    protected function getCommandCachedFile($commandNamespace, $commandTitle)
    {
        return $this->cacheDir . '/commands/' . strtolower(str_replace(array(':', '-'), '_', $commandNamespace.'_'.$commandTitle)).'.php';
    }
    
    public function saveCommandParamters(array $command)
    {
        $commandNamespace = $command['commandNamespace'];
        $commandTitle     = $command['commandTitle'];
        
        unset($command['_token']);
        //unset($command['_token'], $command['commandNamespace'], $command['commandTitle']);
        
        try{
            file_put_contents($this->getCommandCachedFile($commandNamespace, $commandTitle), 
                    $this->normalizeSaveArray("<?php\n\nreturn ".var_export($command, true).";"));
        }
        catch(Exception $e)
        {
            throw new Exception('Cannot write Nocline cache file for commands. 
                Please check [...]/NoclineBundle/Cache/commands folder exists and is writeable.');
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
    
    public function normalizeSaveArray($arrayStr)
    {
        return str_replace(array("'1'"), array('true'), $arrayStr);
    }    
}
