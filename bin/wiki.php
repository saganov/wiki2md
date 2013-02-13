#!/usr/bin/env php
<?php

set_include_path(
    get_include_path() .
    PATH_SEPARATOR .
    __DIR__ . '/../src'
);

require_once 'SplClassLoader.php';
$l = new SplClassLoader('MaxTsepkov');
$l->register();

use MaxTsepkov\Markdown\Text;

if (isset($_SERVER['argv'][1])) {
    if (is_readable($_SERVER['argv'][1]) && is_file($_SERVER['argv'][1])) {
        $md = file_get_contents($_SERVER['argv'][1]);
    }
    else {
        fwrite(STDERR, sprintf('Could not read file "%s" or not a regular file.' . PHP_EOL, $_SERVER['argv'][1]));
        exit(2);
    }
}
else {
    $md = stream_get_contents(STDIN);
}

echo new Text($md);
