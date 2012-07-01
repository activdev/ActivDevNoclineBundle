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
use ActivDev\NoclineBundle\Services\CommandUtil;

class CommandUtilTest extends BaseTestCase
{

    protected $commandUtil;

    public function setUp()
    {
        parent::setUp();

        $this->commandUtil = new CommandUtil($this->_kernel, '0', 'web');
    }

    /**
     *
     * @dataProvider provider_testGetDefinition
     */
    public function testGetDefinition($commandNamespace, $commandTitle)
    {
        $def = $this->commandUtil->getDefinition($commandNamespace, $commandTitle);
        $this->assertTrue($def['command'] == strtolower($commandNamespace.':'.$commandTitle));
    }
    public function provider_testGetDefinition()
    {
        return array
            (
            array('Assets',   'install'),
            array('Doctrine', 'cache:clear-result'),
        );
    }
    
    public function testRun()
    {
        $cmdDef = $this->commandUtil;
        $run    = function($commandNamespace, $commandTitle, $values) use($cmdDef) 
        {
            $definition = $cmdDef->getDefinition($commandNamespace, $commandTitle);
            return $cmdDef->run($definition, $values);
        };
        
        
        $content = $run('Assets', 'install', array(
            '--symlink' =>	'1',
            '_token'    =>	'xxxxxxxxxxxx',
            'target'    =>	'web',
            'commandNamespace'  =>	'Assets',
            'commandTitle'      =>	'install',
        ));
        $this->assertRegExp('#php app/console#', $content, 'The command line used is always in the output');
        $this->assertRegExp('#Installing assets for#', $content);
        
        
        $content = $run('Assets', 'install', array(
            '--symlink' =>	'1',
            '_token'    =>	'xxxxxxxxxxxx',
            'target'    =>	'', // the console must return an error as the target value is required
            'commandNamespace'  =>	'Assets',
            'commandTitle'      =>	'install',
        ));
        $this->assertRegExp('#php app/console#', $content, 'The command line used is always in the output');
        $this->assertRegExp('#<!-- 1 -->#', $content, 'See if the error token is present');
        
    }
    
}
