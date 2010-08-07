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
        'input_encoding' => 'UTF-8',
        'output_encoding' => 'UTF-8',
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
            if (in_array($filter, array('Strip', 'Escape', 'Prune', 'Cull'))) {
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
        $output = $this->_htmlEntityDecode($output, 'UTF-8');
        if (!class_exists('\\tidy') && !$this->_options['disable_tidy']) {
            throw new Wibble\Exception(
                'It is highly recommended that Wibble operate with support from'
                . ' the PHP Tidy extension to ensure output wellformedness. If'
                . ' you are unable to install this extension you may explicitly'
                . ' disable Tidy support by setting the disable_tidy configuration'
                . ' option to FALSE. Without ext/tidy enabled, output is in no'
                . ' way guaranteed to comply entirely to the target HTML standard.'
            );
        } elseif ($this->_options['disable_tidy']) {
            $output = Wibble\Utility::convertFromUTF8($output, $this->_options['output_encoding']);
            return $output;
        }
        $output = $this->_applyTidy($output, true);
        return $output = Wibble\Utility::convertFromUTF8($output, $this->_options['output_encoding']);
    }
    
    /**
     * Based on instance parameters, load the source markup into a \DOMDocument
     *
     * @param string $markup
     * @return void
     */
    protected function _load($markup) {
        $markup = Wibble\Utility::convertToUTF8(
            $markup,
            $this->_options['input_encoding']
        );
        $markup = Wibble\Utility::insertCharset($markup, 'UTF-8');
        $dom = new \DOMDocument;
        $dom->preserveWhitespace = false;
        libxml_use_internal_errors(true);
        $dom->loadHTML($markup);
        libxml_use_internal_errors(false);
        $this->_dom = $dom;
    }
    
    /**
     * Attempts to map a typical encoding name (with mid-slash) to one of the
     * oft custom named encoding parameters accepted by tidy.
     * Note: Technically this is irrevelant since all tidy ops should operate
     * using utf8 once refactoring of this class is complete.
     *
     * @param string $encoding Full name of encoding with slash divisor (e.g. UTF-8)
     * @return string
     */
    protected function _getTidyEncodingFor($encoding) {
        $encoding = strtolower($encoding);
        switch ($encoding) {
            case 'iso-8859-1':
                $return = 'latin1';
                break;
            case 'iso-8859-15':
                $return = 'latin0';
                break;
            default:
                $return = str_replace('-', '', $encoding);
        }
        return $return;
    }
    
    /**
     * Apply html_entity_decode() to a string while re-entitising HTML
     * special char entities to prevent them from being decoded back to their
     * unsafe original forms.
     *
     * This relies on html_entity_decode() not translating entities when
     * doing so leaves behind another entity, e.g. &amp;gt; if decoded would
     * create &gt; which is another entity itself. This seems to escape the
     * usual behaviour where any two paired entities creating a HTML tag are
     * usually decoded, i.e. a lone &gt; is not decoded, but &lt;foo&gt; would
     * be decoded to <foo> since it creates a full tag.
     *
     * Note: This function is poorly explained in the manual - which is really
     * bad given its potential for misuse on user input already escaped elsewhere.
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    protected function _htmlEntityDecode($string, $encoding)
    {
        $string = str_replace(
            array('&gt;', '&lt;', '&amp;', '&quot;', '&#039;'),
            array('&amp;gt;', '&amp;lt;', '&amp;amp;', '&amp;quot;', '&amp;#039;'),
            $string
        );
        $string = html_entity_decode($string, ENT_NOQUOTES, $encoding);
        $string = str_replace(
            array('&amp;gt;', '&amp;lt;', '&amp;amp;', '&amp;quot;', '&amp;#039;'),
            array('&gt;', '&lt;', '&amp;', '&quot;', '&#039;'),
            $string
        );
        return $string;
    }
    
    /**
     * Apply Tidy to output
     *
     * @param string $output
     * @return string
     */
    protected function _applyTidy($output, $bodyOnly = false)
    {
        $tidy = new \tidy;
        $config = array(
            'hide-comments' => true,
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'wrap' => 0,
            'preserve-entities' => true
        );
        if ($bodyOnly) {
            $config['show-body-only'] = true;
        }
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
        $return = trim((string) $tidy);
        return $return;
    }
    
}
