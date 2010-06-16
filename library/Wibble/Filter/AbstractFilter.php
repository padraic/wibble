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

abstract class AbstractFilter implements Filterable
{

    const GO = 'continue';
    
    const STOP = 'stop';
    
    protected $_direction = 'top_down';
    
    public function __construct($direction = null)
    {
        if (!is_null($direction)) {
            $this->setDirection($direction);
        }
    }
    
    public function traverse(\DOMNode $node) {
        $this->_traverseTopDown($node);
    }
    
    public function setDirection($direction)
    {
        if ($direction !== 'top_down' && $direction !== 'bottom_up') {
            throw new Wibble\Exception('Invalid traversal direction. Use "top_down" or "bottom_up"');
        }
        $this->_direction = $direction;
    }
    
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
    
    protected function _sanitize(\DOMNode $node)
    {
        $tagsAllowed = array_merge(
            Whitelist::$acceptableElements,
            Whitelist::$mathmlElements,
            Whitelist::$svgElements,
            Whitelist::$tagsSafeWithLibxml2
        );
        switch ($node->nodeType) {
            case XML_ELEMENT_NODE:
                if (in_array($node->tagName, $tagsAllowed)) {
                    $this->_sanitizeAttributes($node);
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
    
    protected function _sanitizeAttributes(\DOMNode $node)
    {
        $allowedAttributes = array_merge(
            Whitelist::$acceptableAttributes,
            Whitelist::$mathmlAttributes,
            Whitelist::$svgAttributes
        );
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
