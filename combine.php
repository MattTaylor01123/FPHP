<?php

/* 
 * (c) Matthew Taylor
 */

require_once "vendor/autoload.php";

// get file list

$folders = [
    __DIR__ . "/src/collection",
    __DIR__ . "/src/logic",
];

$excludePaths = [
    __DIR__ . "/src/collection/Reduced.php"
];

function getDirFiles(string $path, array $excludePaths)
{
    $it = new DirectoryIterator($path);
    foreach($it as $fileInfo)
    {
        if(!$fileInfo->isDot())
        {
            $out = $path . "/" . $fileInfo->getFilename();
            if(!in_array($out, $excludePaths))
            {
                yield $out;
            }
        }
    }
}

function getAllPaths(iterable $paths, array $excludePaths)
{
    foreach($paths as $path)
    {
        yield from getDirFiles($path, $excludePaths);
    }
}

// convert to fully qualified class names

function convertToFQClassNames(string $base, iterable $paths)
{
    foreach($paths as $path)
    {
        $nsName = str_replace([__DIR__, "/", ".php", "\src"], ["", "\\", "", "FPHP"], $path);
        yield $nsName;
    }
}

// convert to Reflection methods

function toReflectionMethods(iterable $classNames)
{
    foreach($classNames as $className)
    {
        $refClass = new ReflectionClass($className);
        yield from $refClass->getMethods();
    }
}

function getMethodCode(iterable $refMethods)
{
    foreach($refMethods as $refMethod)
    {
        $filename = $refMethod->getFileName();
        $start_line = $refMethod->getStartLine() - 1; // it's actually - 1, otherwise you wont get the function() block
        $end_line = $refMethod->getEndLine();
        $length = $end_line - $start_line;
        $source = file($filename);
        $body = implode("", array_slice($source, $start_line, $length));
        yield $body;
    }
}

// combine all the above
$funcCode = getMethodCode(toReflectionMethods(convertToFQClassNames(__DIR__, getAllPaths($folders, $excludePaths))));
$arrFuncCode = iterator_to_array($funcCode);

// read template contents
$template = file_get_contents(__DIR__ . "/Template.php");
$modified = str_replace(["Template", "// code here"], ["Matt", implode("\r\n", $arrFuncCode)], $template);
$fout = fopen(__DIR__ . "/Matt.php", "w");

fwrite($fout, $modified);
fclose($fout);