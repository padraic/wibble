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
abstract class AbstractFilter implements Filterable
{

    /**
     * Constant representing a GO message
     */
    const GO = 'go';
    
    /**
     * Constant representing a STOP message
     */
    const STOP = 'stop';
    
    /**
     * Dictates the direction of filtering
     *
     * @var string
     */
    protected $_direction = 'top_down';
    
    /**
     * If set to an array, represents a user whitelist which overrides the
     * the internal whitelist
     *
     * @var array
     */
    protected $_userWhitelist = null;
    
    /**
     * Constructor; accepts filtering direction as sole parameter
     *
     * @param string $direction
     */
    public function __construct($direction = null)
    {
        if (!is_null($direction)) {
            $this->setDirection($direction);
        }
    }
    
    /**
     * Traverses the DOM and applies the filter to each encountered node
     *
     * @param \DOMNode $node
     * @return void
     */
    public function traverse(\DOMNode $node)
    {
        $this->_traverseTopDown($node); //TODO - test bottom up
    }
    
    /**
     * Set the filter traversal direction
     *
     * @param string $direction
     */
    public function setDirection($direction)
    {
        if ($direction !== 'top_down' && $direction !== 'bottom_up') {
            throw new Wibble\Exception('Invalid traversal direction. Use "top_down" or "bottom_up"');
        }
        $this->_direction = $direction;
    }
    
    /**
     * Some filters may need specific render option changes. This empty method
     * optionally allows filters to return option arrays to merge into the
     * current options
     *
     * @return array
     */
    public function getRenderOptions()
    {
    }
    
    /**
     * Set a custom whitelist of tags and attributes which overrides the built-in
     * sanitisation whitelist for all any filter utilising sanitisation routines.
     * The whitelist is of the form array('tagName'=>array('attr1', 'attr2'), ...).
     * For DOM purposes, the html and body tags are always whitelisted.
     *
     * @param array $whitelist
     */
    public function setUserWhitelist(array $whitelist)
    {
        if (!array_key_exists('html', $whitelist)) {
            $whitelist['html'] = array();
        }
        if (!array_key_exists('body',$whitelist)) {
            $whitelist['body'] = array();
        }
        if (!array_key_exists('head',$whitelist)) {
            $whitelist['head'] = array();
            $whitelist['meta'] = array('http-equiv','content');
        }
        $this->_userWhitelist = $whitelist;
    }
    
    /**
     * Retrieve the user defined whitelist or NULL if not set
     *
     * @return array|null
     */
    public function getUserWhitelist()
    {
        return $this->_userWhitelist;
    }
    
    /**
     * Traverse the DOM from the top down. If the filter used returns a STOP
     * message, processing of the current node is terminated. This allows for
     * terminating node processing, where the node has been deleted or irrevocably
     * altered so that it no longer can or needs processing.
     *
     * @param \DOMNode $node
     */
    protected function _traverseTopDown(\DOMNode $node)
    {
        if ($this->filter($node) == self::STOP) {
            return;
        }
        $children = $node->childNodes;
        if (!is_null($children) && $children->length > 0) {
            foreach ($children as $child) {
                $this->_traverseTopDown($child);
            }
        }
    }
    
    /**
     * Traverse the DOM from the bottom up. If the filter used returns a STOP
     * message, processing of the current node is terminated. This allows for
     * terminating node processing, where the node has been deleted or irrevocably
     * altered so that it no longer can or needs processing.
     *
     * @param \DOMNode $node
     */
    protected function _traverseBottomUp(\DOMNode $node)
    {
        $children = $node->childNodes;
        if (!is_null($children) && $children->length > 0) {
            foreach ($children as $child) {
                $this->_traverseBottomUp($child);
            }
        }
        if ($this->filter($node) == self::STOP) {
            return;
        }
    }
    
    /**
     * Sanitises the given DOM node. Sanitisation may return either a GO or STOP
     * message. A GO message indicates the current node is a valid element, text
     * or CDATA node. Element tag names are checked against the internal or user
     * defined whitelist. Attributes are also checked against the whitelist. A
     * STOP message indicates an element tag name is not on the whitelist, and
     * allows filters to take an appropriate action such as stripping or escaping
     * the offending node. Attributes which fail the whitelist or internal
     * sanitisation are always stripped.
     *
     * @param \DOMNode $node
     * @return string Message indicating GO or STOP
     */
    protected function _sanitize(\DOMNode $node)
    {
        $userWhitelist = $this->getUserWhitelist();
        if ($userWhitelist === null) {
            $tagsAllowed = array_merge(
                Whitelist::$acceptableElements,
                Whitelist::$mathmlElements,
                Whitelist::$svgElements,
                Whitelist::$tagsSafeWithLibxml2
            );
        } else {
            $tagsAllowed = array_keys($userWhitelist);
        }
        switch ($node->nodeType) {
            case XML_ELEMENT_NODE:
                if (in_array($node->tagName, $tagsAllowed)) {
                    if ($userWhitelist === null) {
                        $this->_sanitizeAttributes($node);
                    } else {
                        $this->_sanitizeAttributes($node, $userWhitelist[$node->tagName]);
                    }
                    return self::GO;
                }
                break;
            case XML_TEXT_NODE:
            case XML_CDATA_SECTION_NODE:
                return self::GO;
                break;
        }
        return self::STOP;
    }
    
    /**
     * Sanitise the attributes of the given nodes according to the internal
     * whitelist and sanitisation checks. A user whitelist may optionally be passed
     * to replace the internal whitelist.
     *
     * @param \DOMNode $node
     * @param array $userWhitelist
     */
    protected function _sanitizeAttributes(\DOMNode $node, array $userWhitelist = null)
    {
        if ($userWhitelist === null) {
            $allowedAttributes = array_merge(
                Whitelist::$acceptableAttributes,
                Whitelist::$mathmlAttributes,
                Whitelist::$svgAttributes
            );
        } else {
            $allowedAttributes = $userWhitelist;
        }
        foreach ($node->attributes as $attribute) {
            if (!empty($attribute->prefix)) {
                $name = $attribute->prefix . ':' . $attribute->name;
            } else {
                $name = $attribute->name;
            }
            if (!in_array($name, $allowedAttributes)) {
                $node->removeAttributeNode($attribute);
                return;
            }
            if (in_array($name, Whitelist::$attributesWithUriValue)) {
                $unescaped = htmlspecialchars_decode($attribute->value);
                $unescaped = strtolower(
                    preg_replace('/[`\000-\040\177-\240\s]+/', '', $unescaped)
                );
                $parts = explode(':', $unescaped);
                $protocol = $parts[0];
                if (preg_match('/^[a-z0-9][-+.a-z0-9]*:/', $unescaped)
                && !in_array($protocol, Whitelist::$acceptableProtocols)) {
                    $node->removeAttributeNode($attribute);
                    return;
                }
            }
            if (in_array($name, Whitelist::$svgAttributeValueAllowsRef)) {
                $node->setAttribute($name,
                    preg_replace(
                        '/url\s*\(\s*[^#\s][^)]+?\)/m', ' ', $node->getAttribute($name)
                    )
                );
            }
            if (in_array($node->tagName, Whitelist::$svgAllowLocalHref)
            && $name == 'xlink:href'
            && preg_match('/^\s*[^#\s].*/m', $node->getAttribute($name))) {
                $node->removeAttributeNode($attribute);
                return;
            }
        }
        if ($node->hasAttribute('style')) {
            $node->setAttribute('style', $this->_sanitizeCSS($node->getAttribute('style')));
        }
    }
    
    /**
     * Sanitise CSS contained by elements and/or attributes.
     *
     * @param string $css The CSS to sanitise
     * @return string The clean sanitised CSS
     */
    protected function _sanitizeCSS($css)
    {
        $css = preg_replace('/url\s*\(\s*[^\s)]+?\s*\)\s*/', ' ', $css);
        if (!preg_match('/^([:,;#%.\sa-zA-Z0-9!]|\w-\w|\'[\s\w]+\'|"[\s\w]+"|\([\d,\s]+\))*$/', $css)) {
            return '';
        }
        if (!preg_match('/^\s*([-\w]+\s*:[^:;]*(;\s*|$))*$/', $css)) {
            return '';
        }
        $clean = array();
        preg_match_all('/([-\w]+)\s*:\s*([^:;]*)/', $css, $matches, PREG_SET_ORDER);
        foreach ($matches as $pairing) {
            if (empty($pairing[2])) continue;
            $prop = explode('-', $pairing[1]);
            if (in_array($pairing[1], Whitelist::$acceptableCssProperties)) {
                $clean[] = $pairing[1] . ': ' . $pairing[2] . ';';
            } elseif (in_array($prop[0], array('background','border','margin','padding'))) {
                $split = explode(' ', $pairing[2]);
                foreach ($split as $term) {
                    if (!in_array($term, Whitelist::$acceptableCssKeywords)
                    && !preg_match('/^(#[0-9a-f]+|rgb\(\d+%?,\d*%?,?\d*%?\)?|\d{0,2}\.?\d{0,2}(cm|em|ex|in|mm|pc|pt|px|%|,|\))?)$/', $term)) {
                        continue 2;
                    }
                }
                $clean[] = $pairing[1] . ': ' . $pairing[2] . ';';
            } elseif (in_array($pairing[1], Whitelist::$allowedSvgProperties)) {
                $clean[] = $pairing[1] . ': ' . $pairing[2] . ';';
            }
        }
        return implode(' ', $clean);
    }

}
