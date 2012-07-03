<?php

/*
 * This file is part of Nocline Bundle.
 *
 * (c) 2012 Bruno ABENA < bruno@activdev.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActivDev\NoclineBundle\Tests\Services;

use ActivDev\NoclineBundle\Tests\BaseTestCase;

class CommandConfigurationTest extends BaseTestCase
{

    /**
     * test command that have a configuration in Resources/commands.php file
     * 
     * @dataProvider provider_testGetJavascript
     */
    public function testGetJavascript($command, $reallyHasJs)
    {
        $js = $this->commandConfiguration->getJavascript($command);
        
        if($reallyHasJs)
        {
            $this->assertRegExp('#type="text/javascript"#', $js);
        }
        else
        {
            //$this->setNoGlobalConfigurationFile();
            $this->assertTrue($js === null);
        }
    }
    public function provider_testGetJavascript()
    {
        return array(
            array('generate:bundle', true),
            array('container:debug', false),
        );
    }

    /**
     * test get data for an arg/opt
     *      
     */
    public function testGetArgOptData()
    {
        /* Auto data */
        
        // bundle arg should contain the list of bundles
        $data = $this->commandConfiguration->getArgOptData('doctrine:mapping:import', 'bundle', true);
        $this->assertTrue(is_array($data) && count($data) > 0);
        
        // bundle arg should not contain the list of bundles becouse it is not required
        $data = $this->commandConfiguration->getArgOptData('doctrine:mapping:import', 'bundle', false);
        $this->assertTrue(empty($data));
        
        /* Data from config in Resources/config/commands.php */
        
        // get "data" config for a arg/opt
        $data = $this->commandConfiguration->getArgOptData('generate:bundle', 'dir', false);
        $this->assertTrue($data == 'src');
        
        // get "data_methods" config for a arg/opt
        $data = $this->commandConfiguration->getArgOptData('generate:bundle', 'format', false);
        $this->assertArrayHasKey('annotation', $data);
        
        
        /* Data from globale config */
        
        // all "em" arg/opt name must be = 1default
        $data = $this->commandConfiguration->getArgOptData('doctrine:cache:clear:query', 'em', false);
        $this->assertTrue($data == 'default');
        $data = $this->commandConfiguration->getArgOptData('doctrine:cache:clear:metadata', 'em', false);
        $this->assertTrue($data == 'default');
        
        // wildcarded arg/opt config
        $data = $this->commandConfiguration->getArgOptData('router:debug', 'name', false);
        $this->assertTrue($data == 'a_name');
        $data = $this->commandConfiguration->getArgOptData('router:dump:apache', 'base-uri', false);
        $this->assertTrue($data == '/test-uri');        
        
        
        /* The command has no config at all */
        $this->setNoGlobalConfigurationFile();
        $data = $this->commandConfiguration->getArgOptData('container:debug', 'show-private', false);
        $this->assertTrue(empty($data));
    }

}
