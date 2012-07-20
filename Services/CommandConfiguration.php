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
    private   $fullConfig;
    
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->setConfigurationFile();
    }
    
    public function setConfigurationFile($configFile = null)
    {
        $this->config = require $configFile ?: __DIR__ . '/../Resources/config/commands.php';
    }
       
    protected function getConfiguration($command) 
    {
        if($this->fullConfig)
        {
            return $this->fullConfig;
        }
        
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
        
        $this->fullConfig = $conf;
        
        return $conf;
    }
    
    /**
     * Automatically fill fields named "bundle" and "entity" with
     * the list of bundles/entities if they are required
     * 
     * @param string $arg_opt
     * @param string $isRequired
     * @return null | array
     */
    protected function getArgOptAutoData($arg_opt, $isRequired) 
    {
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
    
    public function getLoopableArgOpt($command)
    {
        $conf = $this->getArgOptBehaviours($command);
        if(isset($conf['act-as']))
        {
            foreach($conf['act-as'] as $k => $v)
            {
                if(in_array('loopable', $v))
                {
                    return $k;
                }
            }
        }
            
        return null;
    }
    
    protected function getArgOptBehaviours($command, $arg_opt = null)
    {
        $conf = $this->getConfiguration($command);
        
        if(!$arg_opt)
        {
            return $conf;
        }

        if(isset($conf['act-as']) && isset($conf['act-as'][$arg_opt]))
        {
            return $conf['act-as'][$arg_opt];
        }

        return array();
    }
    
    public function hasLoopableBehaviour($command, $arg_opt)
    {
        return in_array('loopable', $this->getArgOptBehaviours($command, $arg_opt));
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
