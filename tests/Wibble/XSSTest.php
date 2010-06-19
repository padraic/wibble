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

class XSSTest extends \PHPUnit_Framework_TestCase
{

    public function setup()
    {
        $this->attacks = simplexml_load_file(dirname(__FILE__) . '/_files/xssAttacks.xml');
    }
    
    public function testAttacksUsingTidy() {
        if (!class_exists('\tidy', false)) {
            $this->markTestSkipped('tidy extension not installed');
        }
        $results = array();
        foreach ($this->attacks->attack as $attack) {
            $code = $attack->code;
            if (substr($code, 0, 7) == 'perl -e') {
                $code = substr($code, $i=strpos($code, '"')+1, strrpos($code, '"') - $i);
                $code = str_replace('\0', "\0", $code);
            }
            if ($code == 'See Below') continue;
            if ($attack->name == 'OBJECT w/Flash 2') continue;
            if ($attack->name == 'IMG Embedded commands 2') continue;
            if ($attack->name == 'US-ASCII encoding') $code = urldecode($code);
            $fragment = new Wibble\HTML\Fragment($code);
            $fragment->filter();
            $results[md5($attack->name)] = $fragment->toString();
        }
        foreach ($results as $hash=>$result) {
            /**
             * Exclude obvious fixes (empty or strings with "XSS" text)
             */
            if ($result == '' || $result == 'XSS') {
                continue;
            }
            switch ($hash) {
                case '063c520e4d665ebd07e25fa25d45b6d9':
                    $this->assertEquals('\';alert(String.fromCharCode(88,83,83))//\\\';alert(String.fromCharCode(88,83,83))//";alert(String.fromCharCode(88,83,83))//\\";alert(String.fromCharCode(88,83,83))//--&gt;"&gt;\'&gt;alert(String.fromCharCode(88,83,83))=&amp;{}', $result);
                    continue;
                    break;
                case '3289d201e73427ed176176ef5a2ae98a':
                    $this->assertEquals('\'\';!--"=&amp;{()}', $result); continue;
                    break;
                case '3fdaccaaa3235525eba5024af896b808':
                    $this->assertEquals('exp/*', $result); continue;
                    break;
                case '668d9c54af72f87798531fab1ed2d8c9':
                    $this->assertEquals('%BCscript%BEalert%28%A2XSS%A2%29%BC%2Fscript%BE', urlencode($result)); continue;
                    break;
                case '7fedad84b66c13f8100e8924da5f42da':
                    $this->assertEquals(']]&gt;', $result); continue;
                    break;
                case '2fec392304a5c23ac138da22847f9b7c':
                    $this->assertEquals('echo(\'alert("XSS")\'); ?&gt;', $result); continue;
                    break;
                case 'ec0d6c5a22c6fa21c793f1c262ddb45b':
                    $this->assertEquals('\";alert(\'XSS\');//', $result); continue;
                    break;
                case '19399eaa4d6a6ada47a4cd1b01a862eb':
                    $this->assertEquals('&amp;', $result); continue;
                    break;
                case '10fed37e21aa207331c8030fb5b11f4f':
                    $this->assertEquals('alert("XSS");//', $result); continue;
                    break;
                case 'a77810f33f3c7bcca0a893ba02a7efe2':
                    $this->assertEquals('alert("XSS")"&gt;', $result); continue;
                    break;
                case 'acaa6f745918544d200d6ab1d3d49dcc':
                    $this->assertEquals('PT SRC="http://ha.ckers.org/xss.js"&gt;', $result); continue;
                    break;
                default:
                    $this->fail('XSS Attack sanitisation failed on ' . $hash);
            }
        }
    }
    
    public function testAttacksWithoutUsingTidy() {
        $results = array();
        foreach ($this->attacks->attack as $attack) {
            $code = $attack->code;
            if (substr($code, 0, 7) == 'perl -e') {
                $code = substr($code, $i=strpos($code, '"')+1, strrpos($code, '"') - $i);
                $code = str_replace('\0', "\0", $code);
            }
            if ($code == 'See Below') continue;
            if ($attack->name == 'OBJECT w/Flash 2') continue;
            if ($attack->name == 'IMG Embedded commands 2') continue;
            if ($attack->name == 'US-ASCII encoding') $code = urldecode($code);
            $fragment = new Wibble\HTML\Fragment($code, array('disable_tidy'=>true));
            $fragment->filter();
            $results[md5($attack->name)] = $fragment->toString();
        }
        foreach ($results as $hash=>$result) {
            /**
             * Exclude obvious fixes (empty or strings with "XSS" text)
             */
            if ($result == '' || $result == 'XSS') {
                continue;
            }
            switch ($hash) {
                case '063c520e4d665ebd07e25fa25d45b6d9':
                    $this->assertEquals('\';alert(String.fromCharCode(88,83,83))//\\\';alert(String.fromCharCode(88,83,83))//";alert(String.fromCharCode(88,83,83))//\\";alert(String.fromCharCode(88,83,83))//--&gt;"&gt;\'&gt;alert(String.fromCharCode(88,83,83))=&amp;{}', $result);
                    continue;
                    break;
                case '3289d201e73427ed176176ef5a2ae98a':
                    $this->assertEquals('\'\';!--"=&amp;{()}', $result); continue;
                    break;
                case '3fdaccaaa3235525eba5024af896b808':
                    $this->assertEquals('exp/*', $result); continue;
                    break;
                case '668d9c54af72f87798531fab1ed2d8c9':
                    $this->assertEquals('&frac14;script&frac34;alert(&cent;XSS&cent;)&frac14;/script&frac34;', $result); continue;
                    break;
                case '7fedad84b66c13f8100e8924da5f42da':
                    $this->assertEquals(']]&gt;', $result); continue;
                    break;
                case '2fec392304a5c23ac138da22847f9b7c':
                    $this->assertEquals('echo(\'alert("XSS")\'); ?&gt;', $result); continue;
                    break;
                case 'ec0d6c5a22c6fa21c793f1c262ddb45b':
                    $this->assertEquals('\";alert(\'XSS\');//', $result); continue;
                    break;
                case '19399eaa4d6a6ada47a4cd1b01a862eb':
                    $this->assertEquals('&amp;', $result); continue;
                    break;
                case '10fed37e21aa207331c8030fb5b11f4f':
                    $this->assertEquals('alert("XSS");//', $result); continue;
                    break;
                case 'a77810f33f3c7bcca0a893ba02a7efe2':
                    $this->assertEquals('alert("XSS")"&gt;', $result); continue;
                    break;
                case 'acaa6f745918544d200d6ab1d3d49dcc':
                    $this->assertEquals('PT SRC="http://ha.ckers.org/xss.js"&gt;', $result); continue;
                    break;
                case '5dd8311a5bb5113e8ac2da6d5c78faf7': // watch this - open tag though URI filtered
                    $this->assertEquals('<?import namespace="t" implementation="#default#time2">', $result); continue;
                    break;
                default:
                    $this->fail('XSS Attack sanitisation failed on ' . $hash);
            }
        }
    }

}
