<?php


namespace Skyblivion\ESReader;


use Eloquent\Enumeration\AbstractEnumeration;

/**
 * Class GRUPPatternPossibility
 * @package Skyblivion\ESReader
 * @method static GRUPPatternPossibility PATTERN_RECORD()
 * @method static GRUPPatternPossibility PATTERN_SUBGROUP()
 */
class GRUPPatternPossibility extends AbstractEnumeration
{
    const PATTERN_RECORD = 1;
    const PATTERN_SUBGROUP = 2;
}