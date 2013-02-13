Notice
======

The library inspired by  [garygolden/markdown-oo-php](https://github.com/garygolden/markdown-oo-php)

What is Markdown?
=================

Markdown is a text-to-HTML conversion tool for web writers.
It is intended to be as easy-to-read and easy-to-write as is feasible.

Readability, however, is emphasized above all else.
A Markdown-formatted document should be publishable as-is, as plain text,
without looking like it’s been marked up with tags or formatting instructions.

See [official website](http://daringfireball.net/projects/markdown/syntax) for syntax.


What is wiki2md?
========================

It's an object-oriented, PSR compatible PHP library capable of converting MediaWiki text to markdown fromat.


Quick start
=========

    set_include_path(get_include_path() . PATH_SEPARATOR . realpath('src'));

    require_once 'SplClassLoader.php';
    $l = new SplClassLoader('MaxTsepkov');
    $l->register();

    use MaxTsepkov\Markdown\Text;

    echo new Text($wiki);

Requirements
===========

  *  PHP  >= 5.3

Contribution
==========

  1.  [Fork me](https://github.com/saganov/wiki2md/fork)
  2.  [Mail me](mailto:saganoff@gmail.com)
