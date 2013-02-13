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
 * Translates paragraphs.
 *
 * Definitions:
 * <ul>
 *   <li>paragraph is simply one or more consecutive lines of text,
 *      separated by one or more blank lines</li>
 *   <li>normal paragraphs should not be indented</li>
 *   <li>block level inline html must be separated with blank lines
 *      and start and end tags should not be indented</li>
 * </ul>
 *
 * @package Markdown
 * @subpackage Filter
 * @author Max Tsepkov <max@garygolden.me>
 * @version 1.0
 */
class Paragraph extends Filter
{
    /**
     * Flag block-level HTML with NOMARKDOWN.
     *
     * @see \Markdown\Filter::preFilter()
     */
    public function preFilter(Text $text)
    {
        $ex = sprintf('/^<(%s)/iuS', implode('|', self::$_blockTags));

        $inHtml = false;
        foreach($text as $no => $line) {
            $prevline = isset($text[$no - 1]) ? $text[$no - 1] : null;
            $nextline = isset($text[$no + 1]) ? $text[$no + 1] : null;

            if (!$inHtml) {
                if (!isset($prevline) || $prevline->isBlank()) {
                    if (preg_match($ex, $line, $matches)) {
                        $inHtml = $matches[1];
                    }
                }
            }

            if ($inHtml) {
                $line->flags |= Line::NOMARKDOWN;
                if (!isset($nextline) || $nextline->isBlank()) {
                    $inHtml = false;
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
        // FIXME
        // code below flags HTML blocks again
        $ex = sprintf('/^<(%s)/iuS', implode('|', self::$_blockTags));

        $inHtml = false;
        foreach($text as $no => $line) {
            $prevline = isset($text[$no - 1]) ? $text[$no - 1] : null;
            $nextline = isset($text[$no + 1]) ? $text[$no + 1] : null;

            if (!$inHtml) {
                if (!isset($prevline) || $prevline->isBlank()) {
                    if (preg_match($ex, $line, $matches)) {
                        $inHtml = $matches[1];
                    }
                }
            }

            if ($inHtml) {
                $line->flags |= Line::NOMARKDOWN;
                if (!isset($nextline) || $nextline->isBlank()) {
                    $inHtml = false;
                }
            }
        }


        $inParagraph = false;

        foreach($text as $no => $line) {
            if ($line->flags & Line::NOMARKDOWN + Line::LISTS) continue;
            if ($line->isBlank()) continue;

            $prevline = isset($text[$no - 1]) ? $text[$no - 1] : null;
            $nextline = isset($text[$no + 1]) ? $text[$no + 1] : null;

            if (!$inParagraph && (!isset($prevline) || $prevline->isBlank())) {
                $line->gist = '' . $line;
                $inParagraph = true;
            }
            if ($inParagraph && (!isset($nextline) || $nextline->isBlank())) {
                $line->gist = $line . '';
                $inParagraph = false;
            }
        }
    }
}
