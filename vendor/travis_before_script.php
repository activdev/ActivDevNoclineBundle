<?php


$buildDir       = realpath(__DIR__.'/../../..'); 
$vendorDir      = __DIR__;
$autoloadFile   = $vendorDir . '/Symfony/app/autoload.php';
$routingFile    = $vendorDir . '/Symfony/app/config/routing.yml';
$appKernelFile  = $vendorDir . '/Symfony/app/AppKernel.php';
$symfonyArchive = 'Symfony_Standard_Vendors_2.0.15.tgz';

//download sf2
echo `wget -O $symfonyArchive http://symfony.com/download?v=$symfonyArchive`;

// extract
echo `tar -xzf vendor/$symfonyArchive -C $vendorDir`;

// install the bundle in the AppKernel.php
file_put_contents($appKernelFile, str_replace(
                       '            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),', 
                       '            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),'.PHP_EOL.
                       '            new ActivDev\NoclineBundle\ActivDevNoclineBundle(),', 
                       file_get_contents($appKernelFile)));

// install the bundle in the autoload.php
file_put_contents($autoloadFile, str_replace(
                       "    'Metadata'         => __DIR__.'/../vendor/metadata/src',", 
                       "    'Metadata'         => __DIR__.'/../vendor/metadata/src',".PHP_EOL.
                       "    'ActivDev'         => __DIR__.'/../../../../..',", 
                       file_get_contents($autoloadFile)));

// install the bundle routing file
file_put_contents($routingFile, "

ActivDevNoclineBundle:
    resource: \"@ActivDevNoclineBundle/Controller/\"
    type:     annotation
    prefix:   /_nocline_    
");

//make dirs match psr-0
echo `mkdir -p $buildDir/ActivDev`;
echo `cp -rf $buildDir/activdev/ActivDevNoclineBundle $buildDir/ActivDev/NoclineBundle`;
