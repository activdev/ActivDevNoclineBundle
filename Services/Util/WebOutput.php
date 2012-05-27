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

use Symfony\Component\Console\Output\StreamOutput;

class WebOutput extends StreamOutput
{
    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = null, OutputFormatterInterface $formatter = null)
    {
        parent::__construct(fopen('php://output', 'w'), $verbosity, $decorated, $formatter);
    }
}