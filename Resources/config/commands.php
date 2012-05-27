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
        'data_methods'=>array
        (
            'format'                => 'self::getListOfConfigFormat'
        ),
    ),
);