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

/**
 * @package    Wibble
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/wibble/blob/master/LICENSE New BSD License
 */
interface Filterable
{

    /**
     * Filter a \DOMNode according to this filter's logic
     *
     * @param \DOMNode $node
     * @return string|null If a string, it should be a GO or STOP message
     */
    public function filter(\DOMNode $node);
    
    /**
     * Traverses the DOM and applies the filter to each encountered node
     *
     * @param \DOMNode $node
     * @return void
     */
    public function traverse(\DOMNode $node);
    
    /**
     * Some filters may need specific render option changes. This empty method
     * optionally allows filters to return option arrays to merge into the
     * current options
     *
     * @return array
     */
    public function getRenderOptions();
    
    /**
     * Set a custom whitelist of tags and attributes which overrides the built-in
     * sanitisation whitelist for all any filter utilising sanitisation routines.
     * The whitelist is of the form array('tagName'=>array('attr1', 'attr2'), ...).
     * For DOM purposes, the html and body tags are always whitelisted.
     *
     * @param array $whitelist
     */
    public function setUserWhitelist(array $whitelist);
    
    /**
     * Retrieve the user defined whitelist or NULL if not set
     *
     * @return array|null
     */
    public function getUserWhitelist();

}
