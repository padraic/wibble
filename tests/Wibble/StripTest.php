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

    /**
     * Batch tests with Tidy (without are further down)
     */

    public function testStripsAllTagsByDefaultWithTidy()
    {
        if (!class_exists('\tidy', false)) $this->markTestSkipped('Tidy unavailable');
        $in = '<p><strong>hello</strong> there</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip');
        $this->assertEquals('hello there', $doc->toString());
    }
    
    public function testStripsAllTagsExceptWhitelistedWithTidy()
    {
        if (!class_exists('\tidy', false)) $this->markTestSkipped('Tidy unavailable');
        $in = '<div><p>I <em>was</em> right <strong>there</strong>!</p></div>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip', array('p'=>array(),'em'=>array()));
        $this->assertEquals('<p>I <em>was</em> right there!</p>', $doc->toString());
    }
    
    public function testStripsAllAttributesByDefaultWithTidy()
    {
        if (!class_exists('\tidy', false)) $this->markTestSkipped('Tidy unavailable');
        $in = '<p class="foo"><strong style="padding-left: 1px">hello</strong> there</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip');
        $this->assertEquals('hello there', $doc->toString());
    }
    
    public function testStripsAllAttributesExceptWhitelistedWithTidy()
    {
        if (!class_exists('\tidy', false)) $this->markTestSkipped('Tidy unavailable');
        $in = '<p class="foo" style="margin-top: 5px">I <em class="foo" style="color: red;">was</em> right there!</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip', array('p'=>array('class'),'em'=>array('style')));
        $this->assertEquals('<p class="foo">I <em style="color: red;">was</em> right there!</p>', $doc->toString());
    }
    
    public function testStripsAllAttributesFromWhitelistedTagsByDefaultWithTidy()
    {
        if (!class_exists('\tidy', false)) $this->markTestSkipped('Tidy unavailable');
        $in = '<p class="foo" style="margin-top: 5px">I <em class="foo" style="color: red;">was</em> right <strong>there</strong>!</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter('strip', array('p'=>array(),'em'=>array()));
        $this->assertEquals('<p>I <em>was</em> right there!</p>', $doc->toString());
    }
    
    public function testStripFilterIsDefaultFilterWithTidy()
    {
        if (!class_exists('\tidy', false)) $this->markTestSkipped('Tidy unavailable');
        $in = '<p class="foo" style="margin-top: 5px">I <em class="foo" style="color: red;">was</em> right <strong>there</strong>!</p>';
        $doc = new Wibble\HTML\Fragment($in);
        $doc->filter(array('p'=>array(),'em'=>array()));
        $this->assertEquals('<p>I <em>was</em> right there!</p>', $doc->toString());
    }
    
    /**
     * Batch tests without Tidy
     */

    public function testStripsAllTagsByDefaultWithoutTidy()
    {
        $in = '<p><strong>hello</strong> there</p>';
        $doc = new Wibble\HTML\Fragment($in, array('disable_tidy'=>true));
        $doc->filter('strip');
        $this->assertEquals('hello there', $doc->toString());
    }
    
    public function testStripsAllTagsExceptWhitelistedWithoutTidy()
    {
        $in = '<div><p>I <em>was</em> right <strong>there</strong>!</p></div>';
        $doc = new Wibble\HTML\Fragment($in, array('disable_tidy'=>true));
        $doc->filter('strip', array('p'=>array(),'em'=>array()));
        $this->assertEquals('<p>I <em>was</em> right there!</p>', $doc->toString());
    }
    
    public function testStripsAllAttributesByDefaultWithoutTidy()
    {
        $in = '<p class="foo"><strong style="padding-left: 1px">hello</strong> there</p>';
        $doc = new Wibble\HTML\Fragment($in, array('disable_tidy'=>true));
        $doc->filter('strip');
        $this->assertEquals('hello there', $doc->toString());
    }
    
    public function testStripsAllAttributesExceptWhitelistedWithoutTidy()
    {
        $in = '<p class="foo" style="margin-top: 5px">I <em class="foo" style="color: red;">was</em> right there!</p>';
        $doc = new Wibble\HTML\Fragment($in, array('disable_tidy'=>true));
        $doc->filter('strip', array('p'=>array('class'),'em'=>array('style')));
        $this->assertEquals('<p class="foo">I <em style="color: red;">was</em> right there!</p>', $doc->toString());
    }
    
    public function testStripsAllAttributesFromWhitelistedTagsByDefaultWithoutTidy()
    {
        $in = '<p class="foo" style="margin-top: 5px">I <em class="foo" style="color: red;">was</em> right <strong>there</strong>!</p>';
        $doc = new Wibble\HTML\Fragment($in, array('disable_tidy'=>true));
        $doc->filter('strip', array('p'=>array(),'em'=>array()));
        $this->assertEquals('<p>I <em>was</em> right there!</p>', $doc->toString());
    }
    
    public function testStripFilterIsDefaultFilterWithoutTidy()
    {
        $in = '<p class="foo" style="margin-top: 5px">I <em class="foo" style="color: red;">was</em> right <strong>there</strong>!</p>';
        $doc = new Wibble\HTML\Fragment($in, array('disable_tidy'=>true));
        $doc->filter(array('p'=>array(),'em'=>array()));
        $this->assertEquals('<p>I <em>was</em> right there!</p>', $doc->toString());
    }

}
