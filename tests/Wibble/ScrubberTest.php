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
        $this->fragmentNodeCount = 4 + 2; // +2 refers to #html and #body added by libxml2 to fragments
        $this->documentNodeCount = 8;
        $this->fragmentTopDownStopCount = 2 - 1; // -1 refers again to libxml2's additions to fragments
        $this->documentTopDownStopCount = 1;
        $self =& $this;
        $this->scrubberGo = new Wibble\Scrubber\Closure(function($node) use ($self) {
            $self->count += 1;
            return \Wibble\Scrubber\AbstractScrubber::GO;
        });
        $this->scrubberStop = new Wibble\Scrubber\Closure(function($node) use ($self) {
            $self->count += 1;
            return \Wibble\Scrubber\AbstractScrubber::STOP;
        });
        $this->scrubberNoFlag = new Wibble\Scrubber\Closure(function($node) use ($self) {
            $self->count += 1;
        });
        $this->scrubberBottomUp = new Wibble\Scrubber\Closure(function($node) use ($self) {
            $self->count += 1;
        }, 'bottom_up');
    }
    
    public function testGoOperatesProperlyOnFragments()
    {
        $document = new Wibble\HTML\Document($this->fragment);
        $document->filterUsing($this->scrubberGo);
        $this->assertEquals($this->fragmentNodeCount, $this->count);
    }
    
    public function testGoOperatesProperlyOnDocuments()
    {
        $document = new Wibble\HTML\Document($this->document);
        $document->filterUsing($this->scrubberGo);
        $this->assertEquals($this->documentNodeCount, $this->count);
    }
    
    public function testStopOperatesProperlyOnFragments()
    {
        $document = new Wibble\HTML\Document($this->fragment);
        $document->filterUsing($this->scrubberStop);
        $this->assertEquals($this->fragmentTopDownStopCount, $this->count);
    }
    
    public function testStopOperatesProperlyOnDocuments()
    {
        $document = new Wibble\HTML\Document($this->document);
        $document->filterUsing($this->scrubberStop);
        $this->assertEquals($this->documentTopDownStopCount, $this->count);
    }
    
    public function testNoFlagOperatesProperlyOnFragments()
    {
        $document = new Wibble\HTML\Document($this->fragment);
        $document->filterUsing($this->scrubberNoFlag);
        $this->assertEquals($this->fragmentNodeCount, $this->count);
    }
    
    public function testBottomUpOperatesProperlyOnFragments()
    {
        $document = new Wibble\HTML\Document($this->fragment);
        $document->filterUsing($this->scrubberBottomUp);
        $this->assertEquals($this->fragmentNodeCount, $this->count);
    }
    
    public function testBottomUpOperatesProperlyOnDocuments()
    {
        $document = new Wibble\HTML\Document($this->document);
        $document->filterUsing($this->scrubberBottomUp);
        $this->assertEquals($this->documentNodeCount, $this->count);
    }
    
    public function testBadTraversalDirectionThrowsException()
    {
        $this->setExpectedException('Wibble\\Exception');
        $scrubber = new Wibble\Scrubber\Closure(function($node) {}, 'foo');
    }

}
