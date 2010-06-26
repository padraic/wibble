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

class ScrubberTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->count = 0;
        $this->fragment = '<span>hello</span><span>goodbye</span>';
        $this->document = '<html><head><link></link></head><body><span>hello</span><span>goodbye</span></body></html>';
        $this->fragmentNodeCount = 4 + 4;
        $this->documentNodeCount = 8;
        $this->fragmentTopDownStopCount = 2 - 1;
        $this->documentTopDownStopCount = 1;
        $self =& $this;
        $this->filterGo = new Wibble\Filter\Closure(function($node) use ($self) {
            $self->count += 1;
            return \Wibble\Filter\AbstractFilter::GO;
        });
        $this->filterStop = new Wibble\Filter\Closure(function($node) use ($self) {
            $self->count += 1;
            return \Wibble\Filter\AbstractFilter::STOP;
        });
        $this->filterNoFlag = new Wibble\Filter\Closure(function($node) use ($self) {
            $self->count += 1;
        });
        $this->filterBottomUp = new Wibble\Filter\Closure(function($node) use ($self) {
            $self->count += 1;
        }, 'bottom_up');
    }
    
    public function testGoOperatesProperlyOnFragments()
    {
        $document = new Wibble\HTML\Document($this->fragment);
        $document->filter($this->filterGo);
        $this->assertEquals($this->fragmentNodeCount, $this->count);
    }
    
    public function testGoOperatesProperlyOnDocuments()
    {
        $document = new Wibble\HTML\Document($this->document);
        $document->filter($this->filterGo);
        $this->assertEquals($this->documentNodeCount, $this->count);
    }
    
    public function testStopOperatesProperlyOnFragments()
    {
        $document = new Wibble\HTML\Document($this->fragment);
        $document->filter($this->filterStop);
        $this->assertEquals($this->fragmentTopDownStopCount, $this->count);
    }
    
    public function testStopOperatesProperlyOnDocuments()
    {
        $document = new Wibble\HTML\Document($this->document);
        $document->filter($this->filterStop);
        $this->assertEquals($this->documentTopDownStopCount, $this->count);
    }
    
    public function testNoFlagOperatesProperlyOnFragments()
    {
        $document = new Wibble\HTML\Document($this->fragment);
        $document->filter($this->filterNoFlag);
        $this->assertEquals($this->fragmentNodeCount, $this->count);
    }
    
    public function testBottomUpOperatesProperlyOnFragments()
    {
        $document = new Wibble\HTML\Document($this->fragment);
        $document->filter($this->filterBottomUp);
        $this->assertEquals($this->fragmentNodeCount, $this->count);
    }
    
    public function testBottomUpOperatesProperlyOnDocuments()
    {
        $document = new Wibble\HTML\Document($this->document);
        $document->filter($this->filterBottomUp);
        $this->assertEquals($this->documentNodeCount, $this->count);
    }
    
    public function testBadTraversalDirectionThrowsException()
    {
        $this->setExpectedException('Wibble\\Exception');
        $filter = new Wibble\Filter\Closure(function($node) {}, 'foo');
    }
    
    public function testBadFilterOnDocumentThrowsException()
    {
        $this->setExpectedException('Wibble\\Exception');
        $document = new Wibble\HTML\Document($this->document);
        $document->filter('foo');
    }

}
