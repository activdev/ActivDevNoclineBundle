<?php

/*
 * This file is part of Nocline Bundle.
 *
 * (c) 2012 Bruno ABENA < bruno@activdev.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return array
(
    
    '*' => array //Global hooks go here, must not be removed.
    (
//        'data_methods'=>array
//        (
//            'bundle'                => 'self::getListOfBundles'
//        ),          
    ),
//    'devkit:*'=>array
//    (
//        'data_methods'=>array
//        (
//            'bundle'                => 'self::getListOfBundles'
//        ),          
//    ),
    
    'doctrine:generate:form'=>array
    (
        'data_methods'=>array
        (
            'entity'                => 'self::getListOfEntities'
        ),
        'act-as' => array
        (
            'entity'                => array('loopable')
        )
    ),
    'assets:install'=>array
    (
        'data'=>array
        (
            'target'                => 'web'
        )          
    ),
    'generate:bundle'=>array
    (
        'javascript'                => 'bundle_name',
        'data_methods'=>array
        (
            'format'                => 'self::getListOfConfigFormat'
        ),
        'data'=>array
        (
            'dir'                   => 'src'
        )          
    ),
    'doctrine:generate:crud'=>array
    (
        'javascript'                => 'route_name',
        'data_methods'=>array
        (
            'format'                => 'self::getListOfConfigFormat',
            'entity'                => 'self::getListOfEntities',
        ),
    ),
    'doctrine:generate:entity'=>array
    (
        'data_methods'=>array
        (
            'format'                => 'self::getListOfConfigFormat'
        ),
    ),
    'doctrine:generate:entities'=>array
    (
        'data_methods'=>array
        (
            'name'                  => 'self::getListOfBundles'
        ),
    ),
    'doctrine:mapping:convert'=>array
    (
        'data_methods'=>array
        (
            'to-type'                => 'self::getListOfConfigFormat'
        ),
    ),
);