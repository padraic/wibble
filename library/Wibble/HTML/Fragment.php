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

class Fragment extends Document
{

    public function __toString()
    {
        $xpath = new \DOMXPath($this->getDOM());
        $result = $xpath->query('/html/body');
        if ($result->length == 0) return '';
        $output = $this->_getInnerHTML($result->item(0));
        if (!class_exists('\\tidy', false) // throw Exception TODO
        || $this->_options['disable_tidy'] === true) {
            return $output;
        }
        $tidy = new \tidy;
        $config = array(
            'hide-comments' => true,
            'show-body-only' => true,
            'input-encoding' => str_replace('-', '', $this->_options['input_encoding']),
            'output-encoding' => str_replace('-', '', $this->_options['output_encoding']),
            'wrap' => 0,
        );
        if (preg_match("/XHTML/", $this->_options['doctype'])) {
            $config['output-xhtml'] = true;
        } else {
            $config['output-html'] = true;
        }
        if (preg_match("/TRANSITIONAL/", $this->_options['doctype'])) {
            $config['doctype'] = 'transitional';
        } else {
            $config['doctype'] = 'strict';
        }
        $tidy->parseString($output, $config);
        $tidy->cleanRepair();
        return trim((string) $tidy);
    }
    
    protected function _getInnerHTML(\DOMNode $node)
    {
        $dom = new \DOMDocument;
        $dom->preserveWhitespace = false;
        $dom->formatOutput = true;
        $children = $node->childNodes;
        if (!is_null($children) && $children->length > 0) {
            foreach ($children as $child) {
                $dom->appendChild($dom->importNode($child, true));
            }
        }
        return trim($dom->saveHTML());
    }

}
