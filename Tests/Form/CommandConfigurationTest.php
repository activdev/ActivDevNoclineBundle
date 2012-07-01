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
use ActivDev\NoclineBundle\Services\Util\BaseCommandConfiguration;

class CommandConfiguration extends BaseCommandConfiguration
{
    protected $config;
    
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->config = require  __DIR__ . '/../Resources/config/commands.php';
    }
    
    public function hasConfiguration($command) 
    {
        return $this->getConfiguration($command) !== null;
    }
    
    protected function getConfiguration($command) 
    {
        //1. the global config namespace
        $conf = $this->config['*'];
        
        foreach ($this->config as $key => $config) 
        {
            //2. get wildcarded hooks config
            if(strpos($key, '*') != false)
            {
                $key = str_replace('*', '', $key);
                if(strpos($command, $key) !== false)
                {
                    $conf = array_merge($conf, $config);
                }
            }
            
            //3. get specific hook config
            if(isset($this->config[$command]))
            {
                $conf = array_merge($conf, $this->config[$command]);
            }
        }
       
        return $conf;
    }
    
    protected function getArgOptAutoData($arg_opt, $isRequired) 
    {
        //watch for those args/opts for some auto magic to happen:
        //bundle, entity
        //echo $arg_opt.' - '.$isRequired."<br>\n";
        if($isRequired)
        {
            if($arg_opt == 'bundle')
            {
                return parent::getListOfBundles();
            }
            if($arg_opt == 'entity')
            {
                return parent::getListOfEntities();
            }
        }

        return null;
    }
    
    public function getJavascript($command) 
    {
        if(!($config = $this->getConfiguration($command)))
        {
            return null;
        }
        
        if(isset($config['javascript']))
        {
            return file_get_contents(__DIR__ . '/../Javascripts/'.$config['javascript'].'.js');
        }
        
        return null;
    }
    
    public function isArgOptRequired($command, $arg_opt, $isRequired) 
    {
        if($config = $this->getConfiguration($command))
        {
            if(isset($config['validation.not_required']) && in_array($arg_opt, $config['validation.not_required']))
            {
                return false;
            }
            if(isset($config['validation.required']) && in_array($arg_opt, $config['validation.required']))
            {
                return true;
            }
        }
        
        return $isRequired;
    }
    
    public function getArgOptData($command, $arg_opt, $isRequired) //for evolution replace isRequired with all validation rules
    {
        $data = $this->getArgOptAutoData($arg_opt, $isRequired);
        
        if(!($config = $this->getConfiguration($command)))
        {
            return $data;
        }
        
        if(isset($config['data_methods']) && isset($config['data_methods'][$arg_opt]))
        {
            $data = call_user_func($config['data_methods'][$arg_opt]);
        }
        elseif(isset($config['data']) && isset($config['data'][$arg_opt]))
        {
           $data = $config['data'][$arg_opt];
        }
        
        return $data;
    }
}
