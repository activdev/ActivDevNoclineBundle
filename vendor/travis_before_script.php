<?php


$buildDir       = realpath(__DIR__.'/../../..'); 
$vendorDir      = __DIR__;
$autoloadFile   = $vendorDir . '/Symfony/app/autoload.php';
$routingFile    = $vendorDir . '/Symfony/app/config/routing.yml';
$appKernelFile  = $vendorDir . '/Symfony/app/AppKernel.php';

//download sf2
//`wget http://symfony.com/download?v=Symfony_Standard_Vendors_2.0.15.tgz`;

`cp /home/bruno/Downloads/Symfony_Standard_Vendors_2.0.14.tgz $vendorDir/`;
// extract
`tar -xzf vendor/Symfony_Standard_Vendors_2.0.14.tgz -C $vendorDir`;

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
`mkdir -p $buildDir/ActivDev`;
`cp -rf $buildDir/activdev/ActivDevNoclineBundle $buildDir/ActivDev/NoclineBundle`;
