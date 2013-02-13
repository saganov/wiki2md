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

namespace MaxTsepkov\Markdown\Filter;

use MaxTsepkov\Markdown\Filter,
    MaxTsepkov\Markdown\Text,
    MaxTsepkov\Markdown\Line;

/**
 * Translate email-style blockquotes.
 *
 * Definitions:
 * <ul>
 *   <li>blockquote is indicated by < at the start of line</li>
 *   <li>blockquotes can be nested</li>
 *   <li>lazy blockquotes are allowed</li>
 *   <li>Blockquote ends with \n\n</li>
 * </ul>
 *
 * @package Markdown
 * @subpackage Filter
 * @author Max Tsepkov <max@garygolden.me>
 * @version 1.0
 */
class Blockquote extends Filter
{
    /**
     * Pass given text through the filter and return result.
     *
     * @see Filter::filter()
     * @param string $text
     * @return string $text
     */
    public function filter(Text $text)
    {
        $stack = null;

        foreach($text as $no => $line) {

            $nextline = isset($text[$no + 1]) ? $text[$no + 1] : null;

            if (!$stack) {
                if (isset($line->gist[0]) && $line->gist[0] == '>') {
                    $stack = new Text();
                }
            }

            if($stack) {
                $line->flags |= Line::BLOCKQUOTE;
                $line->gist   = preg_replace('/^> ?/u', '', $line->gist);
                $stack[$no]   = $line;

                if (!isset($nextline) || $nextline->isBlank()) {
                    $stack = $this->filter($stack);
                    $stack[ key($stack) ]->prepend('<blockquote>');
                    end($stack);
                    $stack[ key($stack) ]->append('</blockquote>');
                    $stack = null;
                }
            }
        }

        return $text;
    }
}
