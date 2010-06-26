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
 * @category   Mockery
 * @package    Mockery
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/wibble/blob/master/LICENSE New BSD License
 */

/**
 * @namespace
 */
namespace WibbleTest;
use Wibble;

class DocumentTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->fragment = '<div>a</div>\n<div>b</div>';
        $this->xml = '<root><div>a</div>\n<div>b</div></root>';
    }
    
    /**
     * Helpers
     */
    protected function getInnerHTML(\DOMNode $node)
    {
        $dom = new \DOMDocument;
        $dom->preserveWhitespace = false;
        $dom->formatOutput = false;
        $children = $node->childNodes;
        foreach ($children as $child) {
            $dom->appendChild($dom->importNode($child, true));
        }
        return trim($dom->saveHTML());
    }
    
    protected function getInnerHTMLFrom(\DOMNode $node, $path) {
        if ($node instanceof \DOMDocument) {
            $rootDoc = $node;
        } else {
            $rootDoc = $node->ownerDocument;
        }
        $xpath = new \DOMXPath($rootDoc);
        $result = $xpath->query($path);
        if ($result->length > 0) {
            return $this->getInnerHTML($result->item(0));
        }
    }
    
    /**
     * Tests
     */
    public function testBasicDocumentWithTidy()
    {
        if (!class_exists('\tidy', false)) $this->markTestSkipped('Tidy unavailable');
        $doc = new Wibble\HTML\Document($this->fragment);
        $this->assertEquals($this->fragment, $this->getInnerHTMLFrom($doc->getDOM(), '/html/body'));
    }
    
    public function testBasicDocumentWithoutTidy()
    {
        $doc = new Wibble\HTML\Document($this->fragment, array('disable_tidy'=>true));
        $this->assertEquals($this->fragment, $this->getInnerHTMLFrom($doc->getDOM(), '/html/body'));
    }
    
    public function testBasicHTMLOutputWithTidy()
    {
        if (!class_exists('\tidy', false)) $this->markTestSkipped('Tidy unavailable');
        $options = array(
            'doctype' => Wibble\HTML\Document::HTML4_TRANSITIONAL
        );
        $doc = new Wibble\HTML\Document(
            '<br>foo',
            $options
        );
        $this->assertRegExp('/<br>foo/', str_replace("\n",'',$doc->toString()));
    }
    
    public function testBasicXHTMLOutputWithTidy()
    {
        if (!class_exists('\tidy', false)) $this->markTestSkipped('Tidy unavailable');
        $options = array(
            'doctype' => Wibble\HTML\Document::XHTML1_STRICT
        );
        $doc = new Wibble\HTML\Document(
            '<br>foo',
            $options
        );
        $this->assertRegExp('/<br \/>foo/', str_replace("\n",'',$doc->toString()));
    }
    
    public function testBasicHTMLOutputWithoutTidy()
    {
        $options = array(
            'doctype' => Wibble\HTML\Document::HTML4_TRANSITIONAL,
            'disable_tidy' => true
        );
        $doc = new Wibble\HTML\Document(
            '<br>foo',
            $options
        );
        $this->assertRegExp('/<br>foo/', str_replace("\n",'',$doc->toString()));
    }
    
    public function testBasicXHTMLOutputWithoutTidy()
    {
        $options = array(
            'doctype' => Wibble\HTML\Document::XHTML1_STRICT,
            'disable_tidy' => true
        );
        $doc = new Wibble\HTML\Document(
            '<br>foo',
            $options
        );
        $this->assertRegExp('/<br>foo/', str_replace("\n",'',$doc->toString()));
    }
    
    public function testDocumentOutputThrowsExceptionIfTidyNotAvailableAndNotDisabledExplicitly()
    {
        if (class_exists('\tidy', false)) $this->markTestSkipped('Tidy installed');
        $doc = new Wibble\HTML\Document($this->fragment);
        $this->setExpectedException('Wibble\Exception');
        $doc->toString();
    }
    
    public function testDocumentOutputDoesNotThrowExceptionIfTidyUnavailableButDisabledExplicitly()
    {
        if (class_exists('\tidy', false)) $this->markTestSkipped('Tidy installed');
        $doc = new Wibble\HTML\Document($this->fragment, array('disable_tidy'=>true));
        $doc->toString();
    }
    
    /**
     * Generic document tests which should work with Fragment (no need to duplicate)
     * to Fragment class - might split to new test file separately for encoding support
     */
    
    public function testHandlesSimpleEncodingPreservation()
    {
        $markup = iconv('UTF-8', 'ISO-8859-15', '<p>€</p>');
        $expected = iconv('UTF-8', 'ISO-8859-15', '€');
        $fragment = new Wibble\HTML\Fragment($markup, array(
            'input_encoding'=>'ISO-8859-15',
            'output_encoding'=>'ISO-8859-15'
        ));
        $fragment->filter();
        $this->assertEquals($expected, $fragment->toString());
    }
    
    public function testHandlesSimpleEncodingConversion()
    {
        $markup = iconv('UTF-8', 'ISO-8859-15', '<p>€</p>');
        $expected = '€'; // UTF-8
        $fragment = new Wibble\HTML\Fragment($markup, array(
            'input_encoding'=>'ISO-8859-15',
            'output_encoding'=>'UTF-8'
        ));
        $fragment->filter();
        $this->assertEquals($expected, $fragment->toString());
    }
    
    /**
     * Personally I'd prefer this didn't happen, but DOM has its own ideas and
     * DOM isn't messed around by hanging quotes. Nonetheless, merging such
     * output with non-filtered HTML would raise the risk of quote escaping a bit.
     */
    public function testEncodingHandlingTranslatesQuoteEquivelantsToRealQuotes()
    {
        $markup = iconv('UTF-8', 'ISO-8859-15', '<p>\'"&quot;&#039;</p>');
        $expected = '\'""\'';
        $fragment = new Wibble\HTML\Fragment($markup, array(
            'input_encoding'=>'ISO-8859-15',
            'output_encoding'=>'UTF-8'
        ));
        $fragment->filter();
        $this->assertEquals($expected, $fragment->toString());
    }

}
