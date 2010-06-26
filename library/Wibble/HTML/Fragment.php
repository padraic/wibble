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

/**
 * @package    Wibble
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/wibble/blob/master/LICENSE New BSD License
 */
class Fragment extends Document
{

    /**
     * Convert this Wibble\HTML\Document to serialised HTML/XHTML string
     *
     * @return string
     */
    public function toString()
    {
        $xpath = new \DOMXPath($this->getDOM());
        $result = $xpath->query('/html/body');
        if ($result->length == 0) return '';
        $output = $this->_getInnerHTML($result->item(0));
        $output = $this->_htmlEntityDecode($output, 'UTF-8');
        if (!class_exists('\\tidy') && !$this->_options['disable_tidy']) {
            throw new Wibble\Exception(
                'It is highly recommended that Wibble operate with support from'
                . ' the PHP Tidy extension to ensure output wellformedness. If'
                . ' you are unable to install this extension you may explicitly'
                . ' disable Tidy support by setting the disable_tidy configuration'
                . ' option to FALSE'
            );
        } elseif ($this->_options['disable_tidy']) {
            $output = Wibble\Utility::convertFromUTF8($output, $this->_options['output_encoding']);
            return $output;
        }
        $output = $this->_applyTidy($output, true);
        return $output = Wibble\Utility::convertFromUTF8($output, $this->_options['output_encoding']);
    }
    
    /**
     * Based on the given \DOMNode, extract its representative HTML/XHTML
     * serialisation.
     *
     * @return string
     */
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
        $output = $dom->saveHTML();
        return trim($output);
    }

}
