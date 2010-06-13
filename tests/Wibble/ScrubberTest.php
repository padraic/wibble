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
        $this->fragmentTopDownStopCount = 2;
        $this->documentTopDownStopCount = 1;
        $self =& $this;
        $this->scrubber = new Wibble\Scrubber\Closure(function($node) use ($self) {
            $self->count += 1;
            return \Wibble\Scrubber\AbstractScrubber::GO;
        });
    }
    
    public function testOperatesProperlyOnFragments()
    {
        $document = new Wibble\HTML\Document($this->fragment);
        $document->filterUsing($this->scrubber);
        $this->assertEquals($this->fragmentNodeCount, $this->count);
    }
    
    public function testOperatesProperlyOnDocuments()
    {
        $document = new Wibble\HTML\Document($this->document);
        $document->filterUsing($this->scrubber);
        $this->assertEquals($this->documentNodeCount, $this->count);
    }

}
