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

class StripTest extends \PHPUnit_Framework_TestCase
{

    public function testStripsAllTagsByDefault()
    {
        $in = '<p><strong>hello</strong> there</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip');
        $this->assertEquals('hello there', $doc->toString());
    }
    
    public function testStripsAllTagsExceptWhitelisted()
    {
        $in = '<div><p>I <em>was</em> right <strong>there</strong>!</p></div>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip', array('p'=>array(),'em'=>array()));
        $this->assertEquals('<p>I <em>was</em> right there!</p>', $doc->toString());
    }
    
    public function testStripsAllAttributesByDefault()
    {
        $in = '<p class="foo"><strong style="padding-left: 1px">hello</strong> there</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip');
        $this->assertEquals('hello there', $doc->toString());
    }
    
    public function testStripsAllAttributesExceptWhitelisted()
    {
        $in = '<p class="foo" style="margin-top: 5px">I <em class="foo" style="color: red;">was</em> right there!</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip', array('p'=>array('class'),'em'=>array('style')));
        $this->assertEquals('<p class="foo">I <em style="color: red;">was</em> right there!</p>', $doc->toString());
    }
    
    public function testStripsAllAttributesFromWhitelistedTagsByDefault()
    {
        $in = '<p class="foo" style="margin-top: 5px">I <em class="foo" style="color: red;">was</em> right <strong>there</strong>!</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip', array('p'=>array(),'em'=>array()));
        $this->assertEquals('<p>I <em>was</em> right there!</p>', $doc->toString());
    }
    
    public function testStripFilterIsDefaultFilter()
    {
        $in = '<p class="foo" style="margin-top: 5px">I <em class="foo" style="color: red;">was</em> right <strong>there</strong>!</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter(array('p'=>array(),'em'=>array()));
        $this->assertEquals('<p>I <em>was</em> right there!</p>', $doc->toString());
    }

}
