<?php

namespace Rosalana\Tracker\Support;

class Fingerprint
{
    public static function make(string ...$parts): string
    {
        return md5(implode('|', $parts));
    }
}