<?php

ini_set('xdebug.var_display_max_depth', '-1');
ini_set('xdebug.var_display_max_children', '-1');
ini_set('xdebug.var_display_max_data', '-1');

function getDirContents($dir, &$results = []) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } elseif ($value != '.' && $value != '..') {
            getDirContents($path, $results);
        }
    }

    return $results;
}

$files = getDirContents(__DIR__ . DIRECTORY_SEPARATOR . '../app');
$map = [];
foreach ($files as $file) {
    if (substr($file, -4) !== '.php') {
        continue;
    }

    $content = file_get_contents($file);
    preg_match('/namespace ([a-zA-Z0-9\\\\]+)/', $content, $m);
    if (isset($m[1])) {
        $parts = explode('\\', $m[1]);
        $a = &$map;
        foreach ($parts as $part) {
            if (!isset($a[$part])) {
                $a[$part] = [];
            }
            $a = &$a[$part];
        }
        $a[] = $file;
    } else {
        var_dump('Error: ' . $file);
    }
}
//var_dump(json_encode($map));
//var_dump($map);
