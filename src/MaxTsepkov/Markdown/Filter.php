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
 * Superclass of all filters.
 *
 * Provides static methods to configure and use filtering system.
 *
 * @package Markdown
 * @subpackage Filter
 * @author Max Tsepkov <max@garygolden.me>
 * @version 1.0
 */
abstract class Filter
{
    /**
     * Empty constructor is used to avoid a bug in PHP 5.3.2
     * GitHub Issue: https://github.com/garygolden/markdown-oo-php/issues/20
     */
    public function __construct(){}
    
    /**
     * List of characters which copies as is after \ char.
     *
     * @var array
     */
    protected static $_escapableChars = array(
        '\\', '`', '*', '_', '{', '}', '[', ']',
        '(' , ')', '#', '+', '-', '.', '!'
    );

    /**
     * Block-level HTML tags.
     *
     * @var array
     */
    protected static $_blockTags = array(
        'p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre',
        'table', 'dl', 'ol', 'ul', 'script', 'noscript', 'form', 'fieldset',
        'iframe', 'math', 'ins', 'del', 'article', 'aside', 'header', 'hgroup',
        'footer', 'nav', 'section', 'figure', 'figcaption'
    );

    abstract public function filter(Text $text);
    public function preFilter(Text $text) {}
    public function postFilter(Text $text) {}
}
