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
 * Represents a piece of text.
 *
 * @package Markdown
 * @subpackage Text
 * @author Max Tsepkov <max@garygolden.me>
 * @version 1.0
 */
class Text extends \ArrayObject
{
    /**
     * Flag indicating that object has been passed through filters.
     *
     * @var bool
     */
    protected $_isFiltered = false;

    protected static $_defaultFilters = null;

    protected static $_factoryDefaultFilters = array(
        'Hr',
        //'ListsBulleted',
        'ListsNumbered',
        'Blockquote',
        'Code',
        //'Entities',
        'HeaderAtx',
        'HeaderSetext',
        'Img',
        'Linebreak',
        'Link',
        'Emphasis',
        'Paragraph',
        'Unescape',
        'Mantis'
    );

    /**
     * Array of custom filters.
     * Default filters are used if not set.
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * Constructor.
     *
     * @param mixed $markdown  String, array or stringable object.
     * @param array $filters   Optional filters instead of defaults.
     * @throws \InvalidArgumentException
     */
    public function __construct($markdown = array(), array $filters = null)
    {
        // break string by newlines, platform-independent
        if (is_string($markdown) || method_exists($markdown, '__toString')) {
            $markdown = explode("\n", (string) $markdown);
            $markdown = array_map(
                function ($markdown) { return trim($markdown, "\r"); },
                $markdown
            );
        }

        if (is_array($markdown)) {
            foreach ($markdown as $no => $value) {
                if ($value instanceof Line) {
                    $this[$no] = $value;
                } else {
                    $this[$no] = new Line($value);
                }
            }
        } else {
            throw new \InvalidArgumentException(
                'Text constructor expects array, string or stringable object.'
            );
        }

        if ($filters !== null) {
            $this->setFilters($filters);
        } else {
            $this->setFilters(self::getDefaultFilters());
        }
    }

    public function __toString()
    {
        return $this->getHtml();
    }

    public function getHtml()
    {
        if (!$this->_isFiltered) {
            foreach ($this->_filters as $filter) {
                $filter->preFilter($this);
            }

            foreach ($this->_filters as $filter) {
                $filter->filter($this);
            }

            foreach ($this->_filters as $filter) {
                $filter->postFilter($this);
            }

            $this->_isFiltered = true;
        }

        return implode("\n", (array) $this);
    }

    public function getFilters()
    {
        return $this->_filters;
    }

    public function offsetSet($index, $newval)
    {
        if ($newval instanceof Line) {
            parent::offsetSet($index, $newval);
        } else {
            $newval = (string) $newval;
            if ($index !== null && isset($this[$index])) {
                // keep existing object
                $this[$index]->gist = $newval;
            }  else {
                // add new element
                parent::offsetSet($index, new Line($newval));
            }
        }
    }

    /**
     * Define filters for this Text instance.
     *
     * Each filter may be defined either as a string or as a Filter instance.
     * If filter is a string, corresponding class will be attempted to autoload.
     *
     * Returns filters array with all members instantiated.
     *
     * @param array $filters
     * @throws \InvalidArgumentException
     * @return array
     */
    public function setFilters(array $filters)
    {
        $this->_filters = array();

        foreach ($filters as $key => $filter) {
            if (is_string($filter) && ctype_alnum($filter)) {
                $classname = __NAMESPACE__ . '\\Filter\\'   . $filter;
                $filter = new $classname;
            }

            if (!$filter instanceof Filter) {
                throw new \InvalidArgumentException(
                    '$filters must be an array which elements ' .
                    'are either an alphanumeric string or a Filter instance'
                );
            }

            $this->_filters[$key] = $filter;
        }

        return $this->_filters;
    }

    public static function getFactoryDefaultFilters()
    {
        return self::$_factoryDefaultFilters;
    }

    public function insert($offset, $lines)
    {
        if (!is_array($lines)) {
            $lines = array($lines);
        }

        $result = (array) $this;

        $slice = array_splice($result, $offset);
        $result = array_merge($result, $lines, $slice);

        foreach ($result as $key => $val) {
            if (!$val instanceof Line) {
                $val = new Line($val);
            }
            $this[$key] = $val;
        }

        return $this;
    }

    /**
     * @return array
     */
    public static function getDefaultFilters()
    {
        if (!self::$_defaultFilters) {
            self::$_defaultFilters = self::$_factoryDefaultFilters;
        }

        return self::$_defaultFilters;
    }

    /**
     * @param array $filters
     * @return Filter
     */
    public static function setDefaultFilters(array $filters)
    {
        self::$_defaultFilters = $filters;
    }
}
