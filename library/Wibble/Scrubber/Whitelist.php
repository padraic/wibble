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
 *  HTML whitelist borrowed from HTML5Lib:
 *
 *    http://code.google.com/p/html5lib/
 *
 *   Copyright (c) 2006-2008 The Authors
 *
 *   Contributors:
 *   James Graham - jg307@cam.ac.uk
 *   Anne van Kesteren - annevankesteren@gmail.com
 *   Lachlan Hunt - lachlan.hunt@lachy.id.au
 *   Matt McDonald - kanashii@kanashii.ca
 *   Sam Ruby - rubys@intertwingly.net
 *   Ian Hickson (Google) - ian@hixie.ch
 *   Thomas Broyer - t.broyer@ltgt.net
 *   Jacques Distler - distler@golem.ph.utexas.edu
 *   Henri Sivonen - hsivonen@iki.fi
 *   The Mozilla Foundation (contributions from Henri Sivonen since 2008)
 *
 *   Permission is hereby granted, free of charge, to any person
 *   obtaining a copy of this software and associated documentation
 *   files (the "Software"), to deal in the Software without
 *   restriction, including without limitation the rights to use, copy,
 *   modify, merge, publish, distribute, sublicense, and/or sell copies
 *   of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be
 *   included in all copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 *   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 *   MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 *   NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 *   HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *   WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *   DEALINGS IN THE SOFTWARE.
 */
class Whitelist
{

    public static $acceptableElements = array(
        'a', 'abbr', 'acronym', 'address', 'area', 'b', 'big', 'blockquote',
        'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup',
        'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'fieldset', 'font',
        'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'input',
        'ins', 'kbd', 'label', 'legend', 'li', 'map', 'menu', 'ol', 'optgroup',
        'option', 'p', 'pre', 'q', 's', 'samp', 'select', 'small', 'span',
        'strike', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea',
        'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul', 'var'
    );

    public static $mathmlElements = array(
        'annotation', 'annotation-xml', 'maction', 'math', 'merror', 'mfrac',
        'mfenced', 'mi', 'mmultiscripts', 'mn', 'mo', 'mover', 'mpadded',
        'mphantom', 'mprescripts', 'mroot', 'mrow', 'mspace', 'msqrt', 'mstyle',
        'msub', 'msubsup', 'msup', 'mtable', 'mtd', 'mtext', 'mtr', 'munder',
        'munderover', 'none', 'semantics'
    );
    
    public static $svgElements = array(
        'a', 'animate', 'animateColor', 'animateMotion', 'animateTransform',
        'circle', 'defs', 'desc', 'ellipse', 'font-face', 'font-face-name',
        'font-face-src', 'foreignObject', 'g', 'glyph', 'hkern', 'linearGradient',
        'line', 'marker', 'metadata', 'missing-glyph', 'mpath', 'path',
        'polygon', 'polyline', 'radialGradient', 'rect', 'set', 'stop', 'svg',
        'switch', 'text', 'title', 'tspan', 'use'
    );
    
    public static $acceptableAttributes = array(
        'abbr', 'accept', 'accept-charset', 'accesskey', 'action', 'align', 'alt',
        'axis', 'border', 'cellpadding', 'cellspacing', 'char', 'charoff',
        'charset', 'checked', 'cite', 'class', 'clear', 'cols', 'colspan',
        'color', 'compact', 'coords', 'datetime', 'dir', 'disabled', 'enctype',
        'for', 'frame', 'headers', 'height', 'href', 'hreflang', 'hspace', 'id',
        'ismap', 'label', 'lang', 'longdesc', 'maxlength', 'media', 'method',
        'multiple', 'name', 'nohref', 'noshade', 'nowrap', 'prompt', 'readonly',
        'rel', 'rev', 'rows', 'rowspan', 'rules', 'scope', 'selected', 'shape',
        'size', 'span', 'src', 'start', 'style', 'summary', 'tabindex', 'target',
        'title', 'type', 'usemap', 'valign', 'value', 'vspace', 'width', 'xml:lang'
    );
    
    public static $tagsSafeWithLibxml2 = array('html', 'head', 'body');
    
    public static $mathmlAttributes = array(
        'actiontype', 'align', 'close', 'columnalign', 'columnalign',
        'columnalign', 'columnlines', 'columnspacing', 'columnspan', 'depth',
        'display', 'displaystyle', 'encoding', 'equalcolumns', 'equalrows',
        'fence', 'fontstyle', 'fontweight', 'frame', 'height', 'linethickness',
        'lspace', 'mathbackground', 'mathcolor', 'mathvariant', 'mathvariant',
        'maxsize', 'minsize', 'open', 'other', 'rowalign', 'rowalign', 'rowalign',
        'rowlines', 'rowspacing', 'rowspan', 'rspace', 'scriptlevel', 'selection',
        'separator', 'separators', 'stretchy', 'width', 'width', 'xlink:href',
        'xlink:show', 'xlink:type', 'xmlns', 'xmlns:xlink'
    );
    
    public static $svgAttributes = array(
        'accent-height', 'accumulate', 'additive', 'alphabetic', 'arabic-form',
        'ascent', 'attributeName', 'attributeType', 'baseProfile', 'bbox', 'begin',
        'by', 'calcMode', 'cap-height', 'class', 'color', 'color-rendering',
        'content', 'cx', 'cy', 'd', 'dx', 'dy', 'descent', 'display', 'dur', 'end',
        'fill', 'fill-opacity', 'fill-rule', 'font-family', 'font-size',
        'font-stretch', 'font-style', 'font-variant', 'font-weight', 'from', 'fx',
        'fy', 'g1', 'g2', 'glyph-name', 'gradientUnits', 'hanging', 'height',
        'horiz-adv-x', 'horiz-origin-x', 'id', 'ideographic', 'k', 'keyPoints',
        'keySplines', 'keyTimes', 'lang', 'marker-end', 'marker-mid', 'marker-start',
        'markerHeight', 'markerUnits', 'markerWidth', 'mathematical', 'max', 'min',
        'name', 'offset', 'opacity', 'orient', 'origin', 'overline-position',
        'overline-thickness', 'panose-1', 'path', 'pathLength', 'points',
        'preserveAspectRatio', 'r', 'refX', 'refY', 'repeatCount', 'repeatDur',
        'requiredExtensions', 'requiredFeatures', 'restart', 'rotate', 'rx', 'ry',
        'slope', 'stemh', 'stemv', 'stop-color', 'stop-opacity',
        'strikethrough-position', 'strikethrough-thickness', 'stroke',
        'stroke-dasharray', 'stroke-dashoffset', 'stroke-linecap',
        'stroke-linejoin', 'stroke-miterlimit', 'stroke-opacity', 'stroke-width',
        'systemLanguage', 'target', 'text-anchor', 'to', 'transform', 'type', 'u1',
        'u2', 'underline-position', 'underline-thickness', 'unicode', 'unicode-range',
        'units-per-em', 'values', 'version', 'viewBox', 'visibility', 'width',
        'widths', 'x', 'x-height', 'x1', 'x2', 'xlink:actuate', 'xlink:arcrole',
        'xlink:href', 'xlink:role', 'xlink:show', 'xlink:title', 'xlink:type',
        'xml:base', 'xml:lang', 'xml:space', 'xmlns', 'xmlns:xlink', 'y', 'y1',
        'y2', 'zoomAndPan'
    );
    
    public static $attributesWithUriValue = array(
        'href', 'src', 'cite', 'action', 'longdesc', 'xlink:href', 'xml:base'
    );
    
    public static $svgAttributeValueAllowsRef = array(
        'clip-path', 'color-profile', 'cursor', 'fill', 'filter', 'marker',
        'marker-start', 'marker-mid', 'marker-end', 'mask', 'stroke'
    );
    
    public static $svgAllowLocalHref = array(
        'altGlyph', 'animate', 'animateColor', 'animateMotion', 'animateTransform',
        'cursor', 'feImage', 'filter', 'linearGradient', 'pattern', 'radialGradient',
        'textpath', 'tref', 'set', 'use'
    );
    
    public static $acceptableCssProperties = array(
        'azimuth', 'background-color', 'border-bottom-color', 'border-collapse',
        'border-color', 'border-left-color', 'border-right-color', 'border-top-color',
        'clear', 'color', 'cursor', 'direction', 'display', 'elevation', 'float',
        'font', 'font-family', 'font-size', 'font-style', 'font-variant',
        'font-weight', 'height', 'letter-spacing', 'line-height', 'overflow', 'pause',
        'pause-after', 'pause-before', 'pitch', 'pitch-range', 'richness', 'speak',
        'speak-header', 'speak-numeral', 'speak-punctuation', 'speech-rate', 'stress',
        'text-align', 'text-decoration', 'text-indent', 'unicode-bidi',
        'vertical-align', 'voice-family', 'volume', 'white-space', 'width'
    );
    
    public static $acceptableCssKeywords = array(
        'auto', 'aqua', 'black', 'block', 'blue', 'bold', 'both', 'bottom', 'brown',
        'center', 'collapse', 'dashed', 'dotted', 'fuchsia', 'gray', 'green',
        '!important', 'italic', 'left', 'lime', 'maroon', 'medium', 'none', 'navy',
        'normal', 'nowrap', 'olive', 'pointer', 'purple', 'red', 'right', 'solid',
        'silver', 'teal', 'top', 'transparent', 'underline', 'white', 'yellow'
    );
    
    public static $acceptableSvgProperties = array(
        'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width',
        'stroke-linecap', 'stroke-linejoin', 'stroke-opacity'
    );
    
    public static $acceptableProtocols = array(
        'ed2k', 'ftp', 'http', 'https', 'irc', 'mailto', 'news', 'gopher', 'nntp',
        'telnet', 'webcal', 'xmpp', 'callto', 'feed', 'urn', 'aim', 'rsync', 'tag',
        'ssh', 'sftp', 'rtsp', 'afs'
    );
    
    public static $voidElements = array(
        'base', 'link', 'meta', 'hr', 'br', 'img', 'embed', 'param', 'area',
        'col', 'input'
    );
    
    public static $tagsSafeWithLibxml2 = array('html', 'head', 'body');

}
