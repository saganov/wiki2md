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
 * Translate code blocks and spans.
 *
 * Definitions of code block:
 * <ul>
 *   <li>code block is indicated by indent at least 4 spaces or 1 tab</li>
 *   <li>one level of indentation is removed from each line of the code block</li>
 *   <li>code block continues until it reaches a line that is not indented</li>
 *   <li>within a code block, ampersands (&) and angle brackets (< and >)
 *      are automatically converted into HTML entities</li>
 * </ul>
 *
 * Definitions of code span:
 * <ul>
 *   <li>span of code is indicated by backtick quotes (`)</li>
 *   <li>to include one or more backticks the delimiters must
 *     contain multiple backticks</li>
 * </ul>
 *
 * @todo Require codeblock to be surrounded by blank lines.
 * @package Markdown
 * @subpackage Filter
 * @author Max Tsepkov <max@garygolden.me>
 * @version 1.0
 */
class Code extends Filter
{
    /**
     * Flags lines containing codeblocks.
     * Other filters must avoid parsing markdown on that lines.
     *
     * @see \Markdown\Filter::preFilter()
     */
    public function preFilter(Text $text)
    {
        foreach($text as $no => $line) {
            if ($line->isIndented()) {
                $line->flags |= Line::NOMARKDOWN + Line::CODEBLOCK;
            } elseif ($line->isBlank()) {
                $prev_no = $no;
                do {
                    $prev_no -= 1;
                    $prevline = isset($text[$prev_no]) ? $text[$prev_no] : null;
                } while ($prevline !== null && $prevline->isBlank());

                $next_no = $no;
                do {
                    $next_no += 1;
                    $nextline = isset($text[$next_no]) ? $text[$next_no] : null;
                } while ($nextline !== null && $nextline->isBlank());

                if ($prevline !== null && $prevline->isIndented() && $nextline !== null && $nextline->isIndented()) {
                    $line->flags |= Line::NOMARKDOWN + Line::CODEBLOCK;
                }
            }

        }
    }

    /**
     * Pass given text through the filter and return result.
     *
     * @see Filter::filter()
     * @param string $text
     * @return string $text
     */
    public function filter(Text $text)
    {
        $insideCodeBlock = false;

        foreach ($text as $no => $line) {
            $nextline = isset($text[$no + 1]) ? $text[$no + 1] : null;

            $nextline = isset($text[$no + 1]) ? $text[$no + 1] : null;

            if ($line->flags & Line::CODEBLOCK) {
                $line->outdent();
                $line->gist = htmlspecialchars($line, ENT_NOQUOTES);
                if (!$insideCodeBlock) {
                    $line->prepend('<pre><code>');
                    $insideCodeBlock = true;
                }
                if (!$nextline || !($nextline->flags & Line::CODEBLOCK)) {
                    $line->append('</code></pre>');
                    $insideCodeBlock = false;
                }
            } else {
                $line->gist = preg_replace_callback(
                    '/(?<!\\\)(`+)(?!`)(?P<code>.+?)(?<!`)\1(?!`)/u',
                    function($values) {
                        $line = trim($values['code']);
                        $line = htmlspecialchars($line, ENT_NOQUOTES);
                        return '<code>' . $line . '</code>';
                    },
                    $line->gist
                );
            }
        }

        return $text;
    }
}
