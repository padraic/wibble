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
     * HTML elements which do not use closing tags (i.e. self-closing)
     */
    protected $nonclosingTags = array(
        'area', 'br', 'hr', 'img', 'input'
    );
    protected $ignoredTags = array(
        'animateColor', 'animateMotion', 'animateTransform', 'foreignObject',
        'linearGradient', 'radialGradient', 'title'
    );
    protected $imposedParentTags = array(
        'dd'=>'dl', 'dt'=>'dl', 'li'=>'ul'
    );
    protected $imposedChildTags = array(
        'dl'=>'dt'
    );

    /**
     * Test Helpers
     */
    
    protected function sanitizeHTMLWithTidy($string)
    {
        $fragment = new Wibble\HTML\Fragment($string);
        $fragment->scrub('escape');
        return $fragment->toString();
    }
    
    protected function sanitizeHTMLWithoutTidy($string)
    {
        $fragment = new Wibble\HTML\Fragment($string, array('disable_tidy'=>true));
        $fragment->scrub('escape');
        return $fragment->toString();
    }
    
    protected function checkSanitizationOfNormalTagWithoutTidy($tag)
    {
        $input       = "<{$tag} title=\"1\">foo <bad>bar</bad> baz</{$tag}>";
        $htmlOutput  = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz</{$tag}>";
        $xhtmlOutput = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz</{$tag}>";
        if (in_array($tag, $this->nonclosingTags)
        || (in_array($tag, array('col')))) {
            $htmlOutput  = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz";
            $xhtmlOutput = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz";
        } elseif (in_array($tag, $this->ignoredTags)) {
            return;
        }
        $sane = $this->sanitizeHTMLWithoutTidy($input);
        $sane = str_replace("\n", '', $sane);
        $this->assertTrue(($htmlOutput == $sane || $xhtmlOutput == $sane), $input);
    }
    
    protected function checkSanitizationOfNormalTagWithTidy($tag)
    {
        if (!class_exists('tidy', false)) {
            $this->markTestSkipped('Tidy extension not installed');
        }
        $input       = "<{$tag} title=\"1\">foo <bad>bar</bad> baz</{$tag}>";
        $htmlOutput  = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz</{$tag}>";
        $xhtmlOutput = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz</{$tag}>";
        if (in_array($tag, $this->nonclosingTags)
        || (in_array($tag, array('col')) && !class_exists('\\tidy', false))) {
            $htmlOutput  = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz";
            $xhtmlOutput = "<{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz";
        } elseif (in_array($tag, $this->ignoredTags)) {
            return;
        }
        /**
         * Set expected output conditionally where departs from default
         */
        if (class_exists('\\tidy', false)) {
            if (in_array($tag, array('caption'))) {
                $htmlOutput = '<table><'. $tag . ' title="1">foo &lt;bad&gt;bar&lt;/bad&gt; baz</' . $tag . '></table>';
                $xhtmlOutput = $htmlOutput;
            } elseif (in_array($tag, array('colgroup'))) {
                $htmlOutput = 'foo &lt;bad&gt;bar&lt;/bad&gt; baz<table><'. $tag . ' title="1"></' . $tag . '></table>';
                $xhtmlOutput = $htmlOutput;
            } elseif (in_array($tag, array('table'))) {
                $htmlOutput = 'foo &lt;bad&gt;bar&lt;/bad&gt; baz<table title="1"></table>';
                $xhtmlOutput = $htmlOutput;
            } elseif (in_array($tag, array('optgroup', 'option', 'tbody', 'tfoot', 'thead'))) {
                $htmlOutput = 'foo &lt;bad&gt;bar&lt;/bad&gt; baz';
                $xhtmlOutput = $htmlOutput;
            } elseif ($tag == 'td') {
                $htmlOutput = '<table><tr><td title="1">foo &lt;bad&gt;bar&lt;/bad&gt; baz</td></tr></table>';
                $xhtmlOutput = $htmlOutput;
            } elseif ($tag == 'th') {
                $htmlOutput = '<table><tr><th title="1">foo &lt;bad&gt;bar&lt;/bad&gt; baz</th></tr></table>';
                $xhtmlOutput = $htmlOutput;
            } elseif ($tag == 'tr') {
                $htmlOutput = 'foo &lt;bad&gt;bar&lt;/bad&gt; baz<table><tr title="1"><td></td></tr></table>';
                $xhtmlOutput = $htmlOutput;
            } elseif ($tag == 'col') {
                $htmlOutput = 'foo &lt;bad&gt;bar&lt;/bad&gt; baz<table><col title="1"></table>';
                $xhtmlOutput = $htmlOutput;
            } elseif ($tag == 'table') {
                $htmlOutput = 'foo &lt;bad&gt;bar&lt;/bad&gt;baz<table title="1"> </table>';
                $xhtmlOutput = $htmlOutput;
            } elseif ($tag == 'image') {
                $htmlOutput = '<img title="1"/>foo &lt;bad&gt;bar&lt;/bad&gt; baz';
                $xhtmlOutput = $htmlOutput;
            } elseif ($tag == 'input') {
                $htmlOutput = '<form><input title="1">foo &lt;bad&gt;bar&lt;/bad&gt; baz</form>';
                $xhtmlOutput = $htmlOutput;
            } elseif (in_array($tag, array('dir', 'menu', 'ol', 'ul'))) {
                $htmlOutput = '<div style="margin-left: 2em" title="1">foo &lt;bad&gt;bar&lt;/bad&gt; baz</div>';
                $xhtmlOutput = $htmlOutput;
            } elseif (in_array($tag, Wibble\Scrubber\Whitelist::$voidElements)) {
                $htmlOutput = '<' . $tag . ' title="1">foo &lt;bad&gt;bar&lt;/bad&gt; baz';
                $xhtmlOutput = $htmlOutput;
            } elseif (isset($this->imposedParentTags[$tag])) {
                $parent = $this->imposedParentTags[$tag];
                $htmlOutput = "<{$parent}><{$tag} title=\"1\">foo &lt;bad&gt;bar&lt;/bad&gt; baz</{$tag}></{$parent}>";
                $xhtmlOutput = $htmlOutput;
            } elseif (isset($this->imposedChildTags[$tag])) {
                $child = $this->imposedChildTags[$tag];
                $htmlOutput = "<{$tag} title=\"1\"><{$child}>foo &lt;bad&gt;bar&lt;/bad&gt; baz</{$child}></{$tag}>";
                $xhtmlOutput = $htmlOutput;
            } elseif (in_array($tag, array('select'))) {
                $htmlOutput = '';
                $xhtmlOutput = $htmlOutput;
            }
        }
        $sane = $this->sanitizeHTMLWithTidy($input);
        $sane = str_replace("\n", '', $sane);
        $this->assertTrue(($htmlOutput == $sane || $xhtmlOutput == $sane), $input);
    }

    /**
     * Tests
     */
    
    public function testAllowsAcceptableElements()
    {
        $acceptableTags = array_merge(
            Wibble\Scrubber\Whitelist::$acceptableElements
        );
        $acceptableXmlTags = array_merge(
            Wibble\Scrubber\Whitelist::$mathmlElements,
            Wibble\Scrubber\Whitelist::$svgElements
        );
        foreach ($acceptableTags as $tag) {
            $this->checkSanitizationOfNormalTagWithTidy($tag);
        }
        foreach ($acceptableTags as $tag) {
            $this->checkSanitizationOfNormalTagWithoutTidy($tag);
        }
        foreach ($acceptableXmlTags as $tag) {
            $this->checkSanitizationOfNormalTagWithoutTidy($tag);
        }
        // todo - XML under tidy
    }

}
