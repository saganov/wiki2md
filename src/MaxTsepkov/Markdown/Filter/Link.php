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
 * Translates links.
 *
 * Definitions:
 * <ul>
 *   <li>link text is delimited by [square brackets]</li>
 *   <li>inline-style URL is inside the parentheses with an optional title in quotes</li>
 *   <li>reference-style links use a second set of square brackets with link label</li>
 *   <li>link definitions can be placed anywhere in document</li>
 *   <li>link definition names may consist of letters, numbers, spaces, and punctuation
 *      — but they are not case sensitive</li>
 * </ul>
 *
 * @package Markdown
 * @subpackage Filter
 * @author Max Tsepkov <max@garygolden.me>
 * @version 1.0
 */
class Link extends Filter
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
        $links = array();
        foreach($text as $no => $line) {
            if (preg_match('/^ {0,3}\[([\w ]+)\]:\s+<?(.+?)>?(\s+[\'"(].*?[\'")])?\s*$/uS', $line, $match)) {
                $link =& $links[ strtolower($match[1]) ];
                $link['href']  = $match[2];
                $link['title'] = null;
                if (isset($match[3])) {
                    $link['title'] = trim($match[3], ' \'"()');
                }
                else if (isset($text[$no + 1])) {
                    if (preg_match('/^ {0,3}[\'"(].*?[\'")]\s*$/uS', $text[$no + 1], $match)) {
                        $link['title'] = trim($match[0], ' \'"()');
                        $text[$no + 1]->gist = '';
                    }
                }
                // erase line
                $line->gist = '';
            }
        }
        unset($link, $match, $no, $line);

        foreach($text as $no => $line) {
            $line->gist = preg_replace_callback(
                //'/\[(.*?)\]\((.*?)(\s+"[\w ]+")?\)/uS',
                '/\[(http.*?)\s+([\w ]+)?\]/uS',
                function ($match) {
                    return Link::buildHtml($match[2], $match[1], NULL);
                },
                $line->gist
            );

            if (preg_match_all('/\[(.+?)\] ?\[([\w ]*)\]/uS', $line, $matches, PREG_SET_ORDER)) {
                foreach($matches as &$match) {
                    $ref = !empty($match[2]) ? $match[2] : $match[1];
                    $ref = strtolower(trim($ref));
                    if (isset($links[$ref])) {
                        $link =& $links[$ref];
                        $html = Link::buildHtml($match[1], $link['href'], $link['title']);
                        $line->gist = str_replace($match[0], $html, $line);
                    }
                }
            }
        }

        return $text;
    }

    public static function buildHtml($content, $href, $title = null)
    {
        $link = '[' . trim($content) . ']';
        if (!empty($title)) {
            //$link .= ' title="' . trim($title, ' "') . '"';
        }
        $link .= ' (' . trim($href) . ')';

        return $link;
    }
}
