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

class Strip extends AbstractFilter
{

    protected $_userWhitelist = array(
        'html' => array(),
        'body' => array()
    );
    
    public function filter(\DOMNode $node)
    {
        if ($this->_sanitize($node) == AbstractFilter::GO) {
            $children = $node->childNodes;
            if (is_null($children) || $children->length == 0) {
                return AbstractFilter::GO;
            }
            for ($i=0;$i<$children->length;$i++) {
                $child = $children->item($i);
                if ($child->nodeType == XML_ELEMENT_NODE) {
                    $this->filter($child);
                }
            }
            return AbstractFilter::GO;
        }
        $children = $node->childNodes;
        if (!is_null($children) && $children->length > 0) {
            for ($i=0;$i<$children->length;$i++) {
                $insert = $children->item($i)->cloneNode(true);
                $node->parentNode->insertBefore(
                    $insert,
                    $node
                );
                $this->filter($insert);
            }
        }
        $node->parentNode->removeChild($node);
    }

}
