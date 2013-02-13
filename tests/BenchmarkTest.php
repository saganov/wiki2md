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

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/../src'));

require_once 'SplClassLoader.php';
$l = new SplClassLoader('MaxTsepkov');
$l->register();

use MaxTsepkov\Markdown\Text;

class BenchmarkTest extends PHPUnit_Framework_TestCase
{
    const MD_SIZE = 1048576; // 1M
    protected static $_markdown;

    protected static $_timings = array();

    /**
     * Generate a large markdown document.
     *
     */
    public static function setUpBeforeClass()
    {
        $charset = "\n\t";
        for ($i = 32; $i <= 126; $i++) {
            $charset .= chr($i);
        }

        $time = microtime(true);
        for ($i = 0; $i < self::MD_SIZE; $i += 64) {
            self::$_markdown .= substr(str_shuffle($charset), 0, 64);
        }
        self::$_timings['generator'] = round(microtime(true) - $time, 4);
    }

    public function testMichelf()
    {
        require_once __DIR__ . '/vendor/michelf/markdown.php';
        $start = microtime(true);
        \Markdown(self::$_markdown);
        self::$_timings['michelf'] = round(microtime(true) - $start, 4);
        $this->addToAssertionCount(1);
    }

    public function testSelf()
    {
        $filters = null;

        $start = microtime(true);
        (string) new Text(self::$_markdown, $filters);
        self::$_timings['self'] = round(microtime(true) - $start, 4);
        $this->addToAssertionCount(1);
    }

    public static function tearDownAfterClass()
    {
        $maxlenKey = max(array_map('strlen', array_keys(self::$_timings)));
        $maxlenVal = max(array_map('strlen', self::$_timings));
        echo PHP_EOL . PHP_EOL;
        echo 'Timings' . PHP_EOL;
        echo str_repeat('-', $maxlenKey + $maxlenVal) . PHP_EOL;
        foreach (self::$_timings as $name => $time) {
            printf("%-${maxlenKey}s : %.4f", ucwords($name), $time);
            echo PHP_EOL;
        }
    }
}
