<?php
/**
 * Copyright (C) 2011, Maxim S. Tsepkov
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/../../src'));

require_once 'SplClassLoader.php';
$l = new SplClassLoader('MaxTsepkov');
$l->register();

use MaxTsepkov\Markdown\Text;

/**
 * Filter test case.
 */
abstract class FilterTestAbstract extends PHPUnit_Framework_TestCase
{
    protected $_className = '';

    public function filesystem()
    {
        $data = array();

        list($classname) = sscanf(get_class($this), 'Filter%sTest');
        $classname = substr($classname, 0, -4);
        $dataDir = __DIR__ . "/../data/$classname";
        unset($classname);

        foreach(glob($dataDir .  '/*.html') as $html) {
            $basename = basename($html);
            $markdown = dirname($html) . '/' . substr($basename, 0, -5);
            if (is_readable($markdown)) {
                $data[] = array(
                    file_get_contents($markdown),
                    file_get_contents($html)
                );
            }
        }

        return $data;
    }

    /**
     * @dataProvider filesystem
     */
    public function testWithData($md, $html)
    {
        $text = new Text($md);
        $this->assertEquals($html, $text->getHtml());
    }
}
