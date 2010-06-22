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
class Document
{

    /**
     * HTML Doctype constants
     */
    const XHTML11             = 'XHTML11';
    const XHTML1_STRICT       = 'XHTML1_STRICT';
    const XHTML1_TRANSITIONAL = 'XHTML1_TRANSITIONAL';
    const HTML4_STRICT        = 'HTML4_STRICT';
    const HTML4_TRANSITIONAL  = 'HTML4_TRANSITIONAL';

    /**
     * The \DOMDocument generated from HTML input
     *
     * @type \DOMDocument
     */
    protected $_dom = null;
    
    /**
     * Options for this class
     *
     * @type array
     */
    protected $_options = array(
        'disable_tidy' => false,
        'doctype' => self::HTML4_TRANSITIONAL,
        'input_encoding' => 'utf-8',
        'output_encoding' => 'utf-8',
    );

    /**
     * Constructor; instantiates object using source markup and given options
     *
     * @param string $markup
     * @param array $options
     */
    public function __construct($markup, array $options = null)
    {
        if (empty($markup)) $markup = ' ';
        if (!is_null($options)) {
            $this->_options = array_merge($this->_options, (array) $options);
        }
        $this->_load($markup);
    }
    
    /**
     * Accepts a \Wibble\Filter\Filterable object or the name of one such
     * built-on class. If omitted, the default filter utilised is
     * \Wibble\Filter\Strip. Optionally, one may pass in a whitelist to
     * override the internal whitelists.
     *
     * @param string|\Wibble\Filter\Filterable|null $filter
     * @param array|null $whitelist
     */
    public function filter($filter = null, array $whitelist = null)
    {
        if (is_array($filter) || empty($filter)) {
            if (empty($filter)) {
                $filter = 'strip';
            } elseif (is_array($filter)) {
                $whitelist = $filter;
                $filter = 'strip';
            }
        }
        $filter = $this->_resolve($filter);
        if (!is_null($whitelist)) {
            $filter->setUserWhitelist($whitelist);
        }
        if (!is_null($this->_dom->documentElement)) {
            $filter->traverse($this->_dom->documentElement);
        }
        return $this;
    }
    
    /**
     * Return the \DOMDocument generated when this class is instantiated
     *
     * @return \DOMDocument
     */
    public function getDOM()
    {
        return $this->_dom;
    }

    /**
     * Resolve the \Wibble\Filter\Filterable object to use based on the user
     * parameters to \Wibble\HTML\Document::filter()
     *
     * @return \Wibble\Filter\Filterable
     * @throws \Wibble\Exception
     */
    protected function _resolve($filter)
    {
        if (is_string($filter)) {
            $filter = ucfirst(strtolower($filter));
        }
        if ($filter instanceof Wibble\Filter\Filterable) {
            return $filter;
        } elseif (is_string($filter)) {
            if (in_array($filter, array('Strip', 'Escape', 'Prune', 'Cull'))) { // delegate out from explicit strings
                $class = 'Wibble\\Filter\\' . $filter;
                $return = new $class;
                return $return;
            }   
        }
        throw new Wibble\Exception('Filter does not exist: ' . (string) $filter);
    }
    
    /**
     * Convert this Wibble\HTML\Document to serialised HTML/XHTML string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Convert this Wibble\HTML\Document to serialised HTML/XHTML string
     *
     * @return string
     */
    public function toString()
    {
        $output = $this->_dom->saveHTML();
        if (!class_exists('\\tidy') && !$this->_options['disable_tidy']) {
            throw new Wibble\Exception(
                'It is highly recommended that Wibble operate with support from'
                . ' the PHP Tidy extension to ensure output wellformedness. If'
                . ' you are unable to install this extension you may explicitly'
                . ' disable Tidy support by setting the disable_tidy configuration'
                . ' option to FALSE'
            );
        } elseif ($this->_options['disable_tidy']) {
            return $output;
        }
        $tidy = new \tidy;
        $config = array(
            'hide-comments' => true,
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
    
    /**
     * Based on instance parameters, load the source markup into a \DOMDocument
     *
     * @param string $markup
     * @return void
     */
    protected function _load($markup) {
        $dom = new \DOMDocument;
        $dom->preserveWhitespace = false;
        $dom->formatOutput = true;
        $dom->recover = 1;
        libxml_use_internal_errors(true);
        $dom->loadHTML($markup);
        libxml_use_internal_errors(false);
        $this->_dom = $dom;
    }
    
}
