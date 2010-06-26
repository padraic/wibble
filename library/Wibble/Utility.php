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

/**
 * @package    Wibble
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/wibble/blob/master/LICENSE New BSD License
 */
class Utility
{

    /**
     * Serialise a node to an XHTML string
     *
     * @param \DOMNode
     * @return string
     */
    public static function nodeToString(\DOMNode $node)
    {
        if ($node->nodeType == XML_CDATA_SECTION_NODE) {
            return '<![CDATA[' . $node->nodeValue . ']]&gt;';
        }
        if ($node->nodeType == XML_TEXT_NODE) {
            return $node->nodeValue;
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
    
    /**
     * Convert a string (e.g. HTML input) to UTF-8, used mainly prior to importing
     * HTML into \DOMDocument and assuming the string is not already supposed to
     * be UTF-8.
     *
     * TODO: Support stuff like SHIFT-JIS
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    public static function convertToUTF8($string, $encoding)
    {
        $encoding = strtoupper($encoding);
        if ($encoding == 'UTF-8') {
            return $string;
        }
        $string = iconv($encoding, 'UTF-8//IGNORE', $string);
        if ($string === false) {
            throw new Wibble\Exception('Encoding not supported: ' . $encoding);
        }
        return $string;
    }
    
    /**
     * Convert a string (e.g. HTML input) from UTF-8, used mainly after exporting
     * HTML from \DOMDocument and assuming the string is not already supposed to
     * be UTF-8. Note that the method of conversion will attempt to add a replacement
     * for any UTF-8 character which cannot be directly represented in the encoding
     * we're converting to. For example, "€" would be converted to "EUR" for
     * ISO-8859-1.
     *
     * TODO: Support stuff like SHIFT-JIS
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    public static function convertFromUTF8($string, $encoding)
    {
        $encoding = strtoupper($encoding);
        if ($encoding == 'UTF-8') {
            return $string;
        }
        $string = iconv('UTF-8', $encoding . '//TRANSLIT', $string);
        if ($string === false) {
            throw new Wibble\Exception('Encoding not supported: ' . $encoding);
        }
        return $string;
    }
    
    /**
     * Add charset to ensure \DOMDocument::loadHTML() does not default to
     * ISO-8859-1 - this is a placeholder only workable on fragments for now.
     *
     * TODO: Replace this quickie stub with a complete functional version
     *
     * @param string $html
     * @param string $encoding
     */
    public static function insertCharset($html, $encoding)
    {
        if (preg_match('/^<html/i', $html)) return $html;
        $encoding = strtoupper($encoding);
        $html = <<<HTML
<html><head><meta http-equiv="Content-Type" content="text/html; charset=$encoding">
</head><body>$html</body></html>
HTML;
        return $html;
    }

}
