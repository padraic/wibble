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

class SanitizeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test Helpers
     */
    
    protected function sanitizeHTML($string)
    {
        $fragment = new Wibble\HTML\Fragment($string);
        $fragment->scrub('escape');
        return $fragment->toString();
    }
    
    protected function checkSanitizationOfNormalTag($tag)
    {
        $input       = "<{$tag} title=\"1\">foo <bad>bar</bad> baz</{$tag}>";
        $htmlOutput  = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz</{$tag}>";
        $xhtmlOutput = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz</{$tag}>";
        $rexmlOutput = $xhtmlOutput;
        $sane = $this->sanitizeHTML($input);
        $this->assertTrue(($htmlOutput == $sane || $xhtmlOutput == $sane || $rexmlOutput == $sane), $input);
    }

    /**
     * Test allowed tags
     */
    
    public function testAllowsATag()
    {
        $this->checkSanitizationOfNormalTag('a');
    }

}
