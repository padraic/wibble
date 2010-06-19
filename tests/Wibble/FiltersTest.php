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

class FiltersTest extends \PHPUnit_Framework_TestCase
{

    public function setup()
    {
        $this->fragment = "<invalid>foo<p>bar</p>bazz</invalid><div>quux</div>";
        $this->escaped = "&lt;invalid&gt;foo&lt;p&gt;bar&lt;/p&gt;bazz&lt;/invalid&gt;<div>quux</div>";
        $this->pruned = "<div>quux</div>";
        $this->culled = "foo<p>bar</p>bazz<div>quux</div>";
    }
    
    public function testEscaping()
    {
        $doc = new Wibble\HTML\Fragment($this->fragment);
        $doc->filter('escape');
        $this->assertEquals($this->escaped, str_replace("\n", '', $doc->toString()));
    }
    
    public function testPruning()
    {
        $doc = new Wibble\HTML\Fragment($this->fragment);
        $doc->filter('prune');
        $this->assertEquals($this->pruned, str_replace("\n", '', $doc->toString()));
    }

    public function testCulling()
    {
        $doc = new Wibble\HTML\Fragment($this->fragment);
        $doc->filter('cull');
        $this->assertEquals($this->culled, str_replace("\n", '', $doc->toString()));
    }

    public function testBasicReturnOfFragmentIsEmptyString()
    {
        $doc = new Wibble\HTML\Fragment('');
        $doc->filter('prune');
        $this->assertEquals('', $doc->toString());
    }
    
    public function testPrunedAllReturnOfFragmentIsEmptyString()
    {
        $doc = new Wibble\HTML\Fragment('<script>foo</script>');
        $doc->filter('prune');
        $this->assertEquals('', $doc->toString());
    }
    
    public function testRemovalOfIllegalTag()
    {
        $html = 'footext<foo>foo</foo>footext';
        $doc = new Wibble\HTML\Document($html);
        $doc->filter('escape');
        $xpath = new \DOMXPath($doc->getDOM());
        $result = $xpath->query('//foo');
        $this->assertEquals(0, $result->length);
    }
    
    public function testRemovalOfIllegalAttribute()
    {
        $html = '<p class=bar foo=bar abbr=bar />';
        $doc = new Wibble\HTML\Document($html);
        $doc->filter('escape');
        $xpath = new \DOMXPath($doc->getDOM());
        $p = $xpath->query('//p[1]')->item(0);
        $this->assertTrue($p->hasAttribute('class'));
        $this->assertTrue($p->hasAttribute('abbr'));
        $this->assertFalse($p->hasAttribute('foo'));
    }
    
    public function testRemovalOfIllegalProtocolUris()
    {
        $html = '<a href="http://www.example.com"></a><a href="data:someuri.com"></a>';
        $doc = new Wibble\HTML\Document($html);
        $doc->filter('escape');
        $xpath = new \DOMXPath($doc->getDOM());
        $result = $xpath->query('//a');
        $this->assertTrue($result->item(0)->hasAttribute('href'));
        $this->assertFalse($result->item(1)->hasAttribute('href'));
    }
    
    public function testRemovalOfIllegalCss()
    {
        $html = '<p style="background-color: url(\'http://foo.com/\'); background-color: #000;" />';
        $doc = new Wibble\HTML\Document($html);
        $doc->filter('escape');
        $xpath = new \DOMXPath($doc->getDOM());
        $result = $xpath->query('//p');
        $this->assertEquals('background-color: #000;', $result->item(0)->getAttribute('style'));
    }

}
