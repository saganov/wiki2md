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

namespace MaxTsepkov\Markdown;

/**
 * Text consist of lines. This is a line.
 *
 * @package Markdown
 * @author Max Tsepkov <max@garygolden.me>
 * @version 1.0
 */
class Line implements \ArrayAccess
{
    const NONE        = 0;
    const NOMARKDOWN  = 1;
    const BLOCKQUOTE  = 2;
    const CODEBLOCK   = 4;
    const HEADER      = 8;
    const HR          = 16;
    const IMG         = 32;
    const LINEBREAK   = 64;
    const LINK        = 128;
    const LISTS       = 256;
    const PARAGRAPH   = 512;

    public $gist  = '';
    public $flags = self::NONE;

    /**
     * Constructor.
     *
     * @param string|object $gist
     * @throws \InvalidArgumentException
     */
    public function __construct($gist = null)
    {
        if ($gist !== null) {
            if (is_string($gist) || method_exists($gist, '__toString')) {
                $this->gist = (string) $gist;
            } else {
                throw new \InvalidArgumentException(
                    'Line constructor expects string or a stringable object.'
                );
            }
        }
    }

    public function __toString()
    {
        return $this->gist;
    }

    public function append($gist)
    {
        $this->gist .= $gist;
        return $this;
    }

    public function prepend($gist)
    {
        $this->gist = $gist . $this->gist;
        return $this;
    }

    public function wrap($tag)
    {
        $this->gist = "<$tag>" . $this->gist . "</$tag>";

        return $this;
    }

    public function outdent()
    {
        $this->gist = preg_replace('/^(\t| {1,4})/uS', '', $this->gist);
        return $this;
    }

    public function isBlank()
    {
        return empty($this->gist) || preg_match('/^\s*$/u', $this->gist);
    }

    public function isIndented()
    {
        if (isset($this->gist[0]) && $this->gist[0] == "\t") {
            return true;
        }
        if (substr($this->gist, 0, 4) == '    ') {
            return true;
        } else {
            return false;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->gist[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->gist[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->gist[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->gist[$offset]);
    }
}
