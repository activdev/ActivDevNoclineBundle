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

use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use ActivDev\NoclineBundle\Services\Util\WebOutput;

class CommandUtil
{
    
    protected $kernel,
              $max_execution_time,
              $execution_mode;
    
    public function __construct(Kernel $kernel, $max_execution_time, $execution_mode)
    {
        $this->kernel             = $kernel;
        $this->max_execution_time = $max_execution_time;
        $this->execution_mode     = $execution_mode;
    }    
    
    public function getDefinition($commandNamespace, $commandTitle)
    {
        $list = static::getListAsArray();

        return $list[$commandNamespace][$commandTitle];
    }
    
    public function run($definition, $values)
    {
        $params = array($definition['command']);
        
        static::formatExecParams('args', $definition, $values, $params);
        static::formatExecParams('opts', $definition, $values, $params);
        
        $params[] = '--no-interaction';
        $content  = $this->execute($params);
        
        return $content;
    }
    
    protected function formatExecParams($type, $definition, $values, &$params)
    {
        foreach($definition[$type] as $name => $def)
        {
            if(isset($values[$name]) && ($value = $values[$name]))
            {
                if($type == 'opts')
                {
                    if($value === '1' && $definition[$type][$name]['type'] == 'checkbox') 
                    {
                        $params[] = $name;
                    }
                    else
                    {
                        if(is_array($value))
                        {
                            foreach($value as $val)
                            {
                                if($v = trim($val))
                                {
                                    $params[] = $name.'='.$v;
                                }
                            }
                        }
                        elseif($v = trim($value))
                        {
                            $params[] = $name.'='.$v;
                        }
                    }
                }
                else
                {
                    $params[] = trim($value);
                }
            }
        }
    }
    
    public function getListAsArray()
    {
        $xmlCommandsList = $this->execute(array('list', '--xml'), true);

        $xmlData  = new \SimpleXMLElement($xmlCommandsList);
        $cmdArray = array();

        foreach($xmlData->commands->command as $n)
        {
            $parts = explode(':', ((string) ($n->attributes()->name)), 2);

            if($parts[0] == 'list' || $parts[0] == 'help')
            {
                continue;
            }
            
            $title = static::getLabel($parts[0]);
            
            if(!isset($cmdArray[$title]))
            {
                $cmdArray[$title] = array();
            }

            $cmdArray[$title][$parts[1]] = array(
                'title'   => static::getLabel(substr((string) $n->attributes()->id, strlen($title))),
                'command' => (string) $n->attributes()->id,
                'description' => (string) $n->description,
            );

            if(!isset($cmdArray[$title][$parts[1]]['args']))
            {
                $cmdArray[$title][$parts[1]]['args'] = array();
            }
            if(!isset($cmdArray[$title][$parts[1]]['opts']))
            {
                $cmdArray[$title][$parts[1]]['opts'] = array();
            }

            foreach($n->arguments->argument as $e)
            {
                $nm = (string) $e->attributes()->name;
                $cmdArray[$title][$parts[1]]['args'][$nm] = static::getElementDefinition($nm, $e, 'arg');
            }

            foreach($n->options->option as $e)
            {
                $nm = (string) $e->attributes()->name;
                $cmdArray[$title][$parts[1]]['opts'][$nm] = static::getElementDefinition($nm, $e, 'opt');
            }
        }

        return $cmdArray;
    }

    protected function getLabel($name)
    {
        return trim(ucfirst(str_replace(array('--', '-', ':', '_'), array('', ' ', ' ', ' '), $name)));
    }

    protected function getElementType($element, $type)
    {
        if(!((string)$element->attributes()->accept_value))
        {
            if($type == 'arg')
            {
                return 'text';
            }
            
            return 'checkbox';
        }
        if(((string)$element->attributes()->is_multiple))
        {
            return 'textmultiple';
        }
        
        return 'text';
    }
    
    protected function getElementDefinition($name, $element, $type)
    {
        $type = static::getElementType($element, $type); 
        $elementDefinition = array
        (
            'name'          => $name,
            'label'         => static::getLabel($name),
            'type'          => $type,
            'placeholder'   => (string) $element->help,
            'help'          => (string) $element->description,
            'value'         => (string) $element->defaults->default,
            'validation'    => array(),
        );

        if($type == 'opt')
        {
            //$isValueAccepted = (string) $element->attributes()->accept_value;
            $isValueRequired = (string) $element->attributes()->is_value_required;

            if(!$isValueRequired)
            {
                $elementDefinition['type'] = 'checkbox';
            }
        }
        
        $elementDefinition['validation']['required'] = (string) $element->attributes()->is_required; // || (string) $element->attributes()->is_value_required;

        return $elementDefinition;
    }
    
    protected function execute(array $params, $isListCommand = false)
    {
        set_time_limit($this->max_execution_time); 
        
        $content    = '';

        //get dirs and go to root dir
        $appDir  = $this->kernel->getRootDir();
        $rootDir = preg_replace('#/app$#', '', $appDir);
        chdir($rootDir);
        
        $cmd_ouput   = '';
        $commandLine = '';
        if(!$isListCommand)
        {
            $sf_params   = $params;
            $sf_cmd      = array_shift($sf_params).' ';
            $commandLine = $this->formatCommandLine($sf_cmd, $sf_params);
            $cmd_ouput   = '>>> '.'php app/console '.escapeshellcmd($commandLine).PHP_EOL.PHP_EOL;
        }
        
        if($this->execution_mode == 'web' || $isListCommand)
        {
            //necessary because array_shift is done in the ArgvInput constructor to remove application name
            array_unshift($params, '');

            $input = new ArgvInput($params);
            $app   = new ConsoleApplication($this->kernel);

            //get the reponse into the buffer
            ob_start(function ($buffer) use(&$content, $isListCommand, $cmd_ouput) 
            {
                if($isListCommand)
                {
                    $content = $buffer; 
                }
                else
                {
                    $content = '<pre>'.$cmd_ouput.$buffer.'</pre>';
                }
                
                return '';
            });

            $app->setAutoExit(false);
            
            //if there is an error echo append the error flag to the response
            if($app->run($input, new WebOutput()))
            {
                echo '<!-- 1 -->';
            }
            
            ob_end_clean();   
        }
        // not sure if this execution mode really usefull at all...
        else if($this->execution_mode == 'console')
        {
            try {
                $p = new Process(escapeshellcmd($appDir.'/console '.$commandLine), $rootDir);
                $p->run();
                $content = '<pre>'.$cmd_ouput.trim($p->getOutput()).trim($p->getErrorOutput()).($p->isSuccessful() ? '':'<!-- 1 -->').'</pre>';
            }
            catch (\Exception $e)
            {
                $content = '<pre>'.$e->getMessage() . PHP_EOL . $p->getErrorOutput().'<!-- 1 -->'.'</pre>';
            }
            
        }
        
        return $content;
    }
    
    protected function formatCommandLine($command, $params)
    {
        foreach($params as $k => $v)
        {
            if(strpos($v, '--') === 0)
            {
                if(strpos($v, '=') !== false)
                {
                    $params[$k] = str_replace('=', '="', $v).'"';
                }
                
                continue;
            }
            
            $params[$k] = '"'.$v.'"';
        }

        return $command.implode(' ', $params);
    }

}
