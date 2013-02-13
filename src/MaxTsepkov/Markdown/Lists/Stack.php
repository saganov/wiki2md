<?php

namespace MaxTsepkov\Markdown\Lists;

use MaxTsepkov\Markdown\Text,
    MaxTsepkov\Markdown\Line;

/**
 *
 * @author Max Tsepkov <max@garygolden.me>
 *
 */
class Stack extends \SplStack
{
    const PARAGRAPH  = 1;
    const BLOCKQUOTE = 2;
    const CODEBLOCK  = 4;

    protected $_paragraphs = false;

    public function apply(Text $text, $tag)
    {
        $listOpened = false;
        $itemOpened = false;
        //echo "$tag\n";
        while (!$this->isEmpty()) {
            $item = $this->shift();
            //var_dump($item);
            // process paragraphs
            if ($this->_paragraphs) {
                $item[ key($item) ] = '<p>' . current($item);
                end($item);
                $item[ key($item) ] = current($item) . '</p>';
                reset($item);
            }

            // process <li>
            $item[ key($item) ] = '<li>' . current($item);
            end($item);
            $item[ key($item) ] = current($item) . '</li>';
            reset($item);

            // process <ul>/<ol>
            if (!$listOpened) {
                $item[ key($item) ] = "<$tag>\n" . current($item);
                $listOpened = true;
            }

            foreach ($item as $no => $line) {
                $line = new Line($line);
                $line->flags |= Line::LISTS;
                $text[$no] = $line;
            }
        }
        //echo "========\n";
        $text[$no]->gist = $text[$no] . "\n</$tag>";

        $this->reset();

        return $text;
    }

    public function addItem(array $lines)
    {
        $this->push($lines);

        return $this;
    }

    public function appendLine(array $line, $flags = 0)
    {
        $item = $this->pop();
        $item += $line;
        $this->push($item);

        return $this;
    }

    public function paragraphize($bool = true)
    {
        $this->_paragraphs = (bool) $bool;

        return $this;
    }

    public function reset()
    {
        while (!$this->isEmpty()) {
            $this->pop();
        }

        $this->_paragraphs = false;

        return $this;
    }

    public function toArray()
    {
        $result = array();

        foreach($this as $key => $val) {
            $result[$key] = $val;
        }

        return $result;
    }
}
