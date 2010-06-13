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
namespace Wibble\HTML;
use Wibble;

class Document
{

    protected $_dom = null;

    public function __construct($markup)
    {
        $this->_dom = new \DOMDocument;
        $this->_dom->preserveWhitespace = false;
        $this->_dom->formatOutput = true;
        $this->_dom->recover = 1;
        libxml_use_internal_errors(true);
        $this->_dom->loadHTML($markup);
        libxml_use_internal_errors(false);
    }
    
    public function scrub($scrubber)
    {
        $scrubber = $this->_resolve($scrubber);
        $scrubber->traverse($this->_dom->documentElement);
        return $this;
    }
    
    public function getDOM()
    {
        return $this->_dom;
    }

    protected function _resolve($scrubber)
    {
        if (is_string($scrubber)) {
            $scrubber = ucfirst(strtolower($scrubber));
        }
        if ($scrubber instanceof Wibble\Scrubber\Scrubbable) {
            return $scrubber;
        } elseif (is_string($scrubber)) {
            if (in_array($scrubber, array('Strip', 'Escape'))) { // delegate out from explicit strings
                $class = 'Wibble\\Scrubber\\' . $scrubber;
                $return = new $class;
                return $return;
            }   
        }
        throw new Wibble\Exception('Scrubber does not exist: ' . (string) $scrubber);
    }
    
    public function toString()
    {
        return (string) $this;
    }
    
    public function __toString()
    {
        return $this->_dom->saveHTML();
    }
}
