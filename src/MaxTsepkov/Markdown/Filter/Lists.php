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

use MaxTsepkov\Markdown\Lists\Stack,
    MaxTsepkov\Markdown\Filter,
    MaxTsepkov\Markdown\Text,
    MaxTsepkov\Markdown\Line;

/**
 * Abstract class for all list's types
 *
 * Definitions:
 * <ul>
 *   <li>list items may consist of multiple paragraphs</li>
 *   <li>each subsequent paragraph in a list item
 *      must be indented by either 4 spaces or one tab</li>
 * </ul>
 *
 * @todo Readahead list lines and pass through blockquote and code filters.
 * @package Markdown
 * @subpackage Filter
 * @author Max Tsepkov <max@garygolden.me>
 * @version 1.0
 */
abstract class Lists extends Filter
{
    /**
     * array( intendation level => list level );
     */
    protected static $levels = array(0 => 1);
    protected static $intend = 0;
    protected static $level  = 1;

    /**
     * Pass given text through the filter and return result.
     *
     * @see Filter::filter()
     * @param string $text
     * @return string $text
     */
    public function filter(Text $text)
    {
        foreach($text as $no => $line) {
            //if ($line->flags & Line::NOMARKDOWN) continue;
            if (false !== $matches = $this->matchMarker($line)) {
                $line->flags = Line::LISTS;
                $html = trim(ltrim((string)$line, $matches[2].' '));
                $intend = strlen($matches[1]);
                
                if($intend > self::$intend)
                {
                    self::$levels[$intend] = ++self::$level;
                }
                elseif($intend < self::$intend)
                {
                    if(isset(self::$levels[$intend]))
                    {
                        self::$level = self::$levels[$intend];
                    }
                    else
                    {
                        // try to correct wrong intendation
                        // try to gues how intendation should be instead
                        ksort(self::$levels);
                        for($i=1; $i<5; $i++)
                        {
                            if(isset(self::$levels[$intend-$i]))
                            {
                                self::$level = self::$levels[$intend-$i];
                                break;
                            }
                            else if(isset(self::$levels[$intend+$i]))
                            {
                                self::$level = self::$levels[$intend+$i];
                                break;
                            }
                        }
                        // ERROR
                    }
                }
                else
                {
                    
                }
                
                self::$intend = $intend;
                $line->gist = str_repeat(static::MARKER, self::$level). " {$html}";
            }
        }
    }

    //abstract protected function matchMarker($line);
    protected function matchMarker($line)
    {
        if (preg_match(static::REGEXP, $line, $matches)) {
            return $matches;
        } else {
            return false;
        }
    }
}
