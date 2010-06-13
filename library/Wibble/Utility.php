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
namespace Wibble;

class Utility
{

    public static function nodeToString(\DOMNode $node)
    {
        if ($node->nodeType == XML_CDATA_SECTION_NODE) {
            return '<![CDATA[' . $node->nodeValue . ']]&gt;';
        }
        if (preg_match("/^\#/", $node->nodeName)) {
            return '';
        }
        $string = '<' . $node->tagName;
        $attributes = $node->attributes;
        if (!is_null($attributes)) {
            foreach ($attributes as $attribute) {
                $string .= ' ' . $attribute->name . '="' . $attribute->value . '"';
            }
        }
        $children = $node->childNodes;
        if (is_null($children) || $children->length == 0) {
            $text = $node->textContent;
            if (!is_null($text) && $text !== '') {
                $string .= $text . '</' . $node->tagName . '>';
            } else {
                $string .= '/>';
            }
        } else {
            $string .= '>';
            $hasValidChildren = false;
            for ($i=0;$i<$children->length;$i++) {
                $childToString = self::nodeToString($children->item($i));
                if ($childToString !== '') {
                    $string .= $childToString;
                    $hasValidChildren = true;
                }
            }
            $text = $node->textContent;
            if (!$hasValidChildren && !is_null($text)) {
                $string .= $text;
            }
            $string .= '</' . $node->tagName . '>';
        }
        return $string;
    }

}
