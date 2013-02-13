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
    MaxTsepkov\Markdown\Text;

/**
 * Translates & and &lt; to &amp;amp; and &amp;lt;
 *
 * Definitions:
 * <ul>
 *   <li>Transform & to &amp;amp; and < to &amp;lt;</li>
 *   <li>do NOT transform if & is part of html entity, e.g. &amp;copy;</li>
 *   <li>do NOT transform < if it's part of html tag</li>
 *   <li>ALWAYS transfrom & and < within code spans and blocks</li>
 * </ul>
 *
 * @package Markdown
 * @subpackage Filter
 * @author Max Tsepkov <max@garygolden.me>
 * @version 1.0
 */
class Command extends Filter
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
        foreach($text as $no => $line) {
            foreach($this->allowedCommands() as $method=>$command)
            {
                if(preg_match('/^(.*?)'. $command .':\s*([\w_][\w\d\._]+)\s*(\{.*?\})?(.*?)$/uS', $line, $matches))
                {
                    $line->gist = $matches[1]
                        . (string) call_user_func(array($this, $method), $matches[2], $matches[3])
                        . $matches[4];
                }
            }
        }
    }

    protected function allowedCommands()
    {
        $methods = array();
        $class = new \ReflectionClass($this);
        foreach($class->getMethods(\ReflectionMethod::IS_PROTECTED) as $method)
        {
            if(preg_match('/^command([\w_]+)$/', $method->name, $matched))
            {
                $methods[$matched[0]] = strtolower($matched[1]);
            }
        }
        
        return $methods;
    }
   
    private function insertFileContent($file, $isRequired = FALSE, $options = '')
    {
        $start_marker = $stop_marker = '';
        if('' !== $options)
        {
            list($start_marker, $stop_marker) = array_map(function($marker){
                    $marker = trim($marker);
                    return (substr($marker, 0,1) == '/' && substr($marker, -1,1) == '/' ? trim($marker, '/') : preg_quote($marker));
                }
                , explode('-', trim($options, '{}')));
        }

        $working_directory = Text::getWorkingDirectory();
        $file = (!empty($working_directory) ? $working_directory.'/' : '') . $file;
        if(is_readable($file) && is_file($file))
        {
            $md = file_get_contents($file);
            if((bool)$start_marker || (bool)$stop_marker)
            {
                $regexp = '/'. $start_marker. '(.*)'. $stop_marker .'/Ums';
                if(preg_match($regexp, $md, $matches))
                {
                    $md = trim($matches[1]);
                }
                elseif($isRequired)
                {
                    throw new \Exception("Retrieved sequences: '{$options}' not found in file '{$file}'");
                }
                else
                {
                    return '';
                }
            }

            $text = new Text($md);
            return (string) $text;
        }
        elseif($isRequired)
        {
            throw new \Exception("Retrieved file '{$file}' not found or not exists");
        }
        else
        {
            return '';
        }
    }
 
    protected function commandInclude($file, $options)
    {
        return $this->insertFileContent($file, FALSE, $options);
    }

    protected function commandRequire($file, $options)
    {
        return $this->insertFileContent($file, TRUE, $options);
    }

    protected function commandMantis($number, $options)
    {
        return "{{M|$number}}";
    }
}
