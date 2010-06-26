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
namespace Wibble\Filter;
use Wibble;

/**
 * @package    Wibble
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/wibble/blob/master/LICENSE New BSD License
 */
class Escape extends AbstractFilter
{
    
    /**
     * Filter a \DOMNode according to this filter's logic
     *
     * @param \DOMNode $node
     * @return string|null If a string, it should be a GO or STOP message
     */
    public function filter(\DOMNode $node)
    {
        if ($this->_sanitize($node) == AbstractFilter::GO) {
            return AbstractFilter::GO;
        }
        $replacementText = Wibble\Utility::nodeToString($node);
        $replacementNode = $node->ownerDocument->createTextNode($replacementText);
        $parent = $node->parentNode;
        $parent->insertBefore($replacementNode, $node);
        $parent->removeChild($node);
    }

}
