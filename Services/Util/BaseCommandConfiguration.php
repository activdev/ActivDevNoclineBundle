<?php

/*
 * This file is part of Nocline Bundle.
 *
 * (c) 2012 Bruno ABENA < bruno@activdev.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActivDev\NoclineBundle\Services\Util;

abstract class BaseCommandConfiguration
{

    function getListOfConfigFormat()
    {
        return array
        (
            'annotation' => 'annotation',
            'yml'        => 'yml',
            'php'        => 'php',
            'xml'        => 'xml',
        );
    }

}