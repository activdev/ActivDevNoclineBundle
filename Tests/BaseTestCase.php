<?php

namespace ActivDev\NoclineBundle\Tests;

use ActivDev\NoclineBundle\Services\CommandConfiguration;

class BaseTestCase extends \PHPUnit_Framework_TestCase
{

    protected $_container;
    protected $_kernel;
    
    /**
     *
     * @var CommandConfiguration
     */
    protected $commandConfiguration;

    public function setUp()
    {
        $this->_kernel = new \AppKernel("test", true);
        $this->_kernel->boot();
        
        $this->_container = $this->_kernel->getContainer();
        
        $this->commandConfiguration = new CommandConfiguration($this->_kernel);
        $this->setDefaultConfigurationFile();
    }

    protected function get($service)
    {
        return $this->_container->get($service);
    }

    protected function setDefaultConfigurationFile()
    {
        $this->commandConfiguration->setConfigurationFile(__DIR__ . '/config/cmd_default.php');
    }
    protected function setNoGlobalConfigurationFile()
    {
        $this->commandConfiguration->setConfigurationFile(__DIR__ . '/config/cmd_no_global.php');
    }

}