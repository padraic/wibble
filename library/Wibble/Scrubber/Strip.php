<?php
/**
 * Wibble
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://github.com/padraic/wibble/blob/master/LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to padraic@php.net so we can send you a copy immediately.
 *
 * @category   Wibble
 * @package    Wibble
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/wibble/blob/master/LICENSE New BSD License
 */
 
/**
 * @namespace
 */
namespace Wibble\Scrubber;
use Wibble;

class Strip extends AbstractScrubber
{
    
    public function scrub(\DOMNode $node)
    {
        if ($this->_sanitize($node) == AbstractScrubber::GO) {
            return AbstractScrubber::GO;
        }
        foreach ($node->childNodes as $child) {
            $node->parentNode->insertBefore($child, $node);
        }
        $node->parentNode->removeChild($node);
    }

}