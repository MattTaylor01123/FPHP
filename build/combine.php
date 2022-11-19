<?php

/* 
 * (c) Matthew Taylor
 */

$rootPath = realpath(__DIR__ . "/..");
error_log($rootPath);

require_once $rootPath . "/vendor/autoload.php";

// get file list
$folders = [
    $rootPath . "/src/collection",
    $rootPath . "/src/logic",
    $rootPath . "/src/Functions.php",
    $rootPath . "/src/Memoize.php",
    $rootPath . "/src/Predicates.php",
    $rootPath . "/src/Reducing.php"
];

$excludePaths = [
    $rootPath . "/src/collection/Reduced.php"
];

function getDirFiles(string $path, array $excludePaths)
{
    if(str_contains($path, ".php"))
    {
        yield $path;
    }
    else
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
        $nsName = str_replace([$base, "/", ".php", "\src"], ["", "\\", "", "src"], $path);
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
        $comment = $refMethod->getDocComment();
        if($comment)
        {
            yield "    $comment";
        }
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
$funcCode = getMethodCode(toReflectionMethods(convertToFQClassNames($rootPath, getAllPaths($folders, $excludePaths))));
$arrFuncCode = iterator_to_array($funcCode);

// read template contents
$template = file_get_contents(__DIR__ . "/Template.php");
$modified = str_replace(["Template", "// code here"], ["Matt", implode("\r\n", $arrFuncCode)], $template);
$fout = fopen($rootPath . "/bin/FPHP.php", "w");

fwrite($fout, $modified);
fclose($fout);