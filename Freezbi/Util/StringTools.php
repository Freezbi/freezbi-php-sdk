<?php
namespace Freezbi\Util;

class StringTools
{
    public static function clearFilename($filename)
    {
        if ($filename == "::1" || $filename == "127.0.0.1") {
            return 1;
        }

        $bad = array_merge(array_map('chr', range(0, 31)), array("<", ">", ":", '"', "/", "\\", "|", "?", "*"));
        return str_replace($bad, "", $filename);
    }



    public static function startsWith($hay, $needle)
    {
        return substr($hay, 0, strlen($needle."")) === $needle."";
    }

    public static function endsWith($hay, $needle)
    {
        return substr($hay, -strlen($needle."")) === $needle."";
    }
}
