<?php

namespace Skyblivion\ESReader\TES4;


use Skyblivion\ESReader\Exception\InvalidESFileException;
use Skyblivion\ESReader\GRUPPattern;
use Skyblivion\ESReader\GRUPPatternPossibility;
use Skyblivion\ESReader\GRUPPatternRecord;

/**
 * Represents top level GRUP
 * Class TES4Grup
 * @package Skyblivion\ESReader\TES4
 */
class TES4Grup
{
    const GRUP_HEADER_SIZE = 20;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $type;

    /**
     * @var GRUPPattern
     */
    private static $customPatterns;

    private static $defaultPattern;

    public function __construct()
    {
        if(self::$customPatterns === null)
        {
            self::$customPatterns = [
              'DIAL' => new GRUPPattern(
                  [
                      //DIAL
                      new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_RECORD(),null),
                      //INFO subgroup
                      new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_SUBGROUP(), new GRUPPattern(
                          [
                              new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_RECORD(), null)
                          ]
                      ))
                  ]
              ),
              'CELL' => new GRUPPattern(
                  [
                      //nested block
                      new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_SUBGROUP(), new GRUPPattern(
                          [
                              //nested subblock
                             new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_SUBGROUP(), new GRUPPattern(
                                 [
                                    new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_RECORD(),null),
                                    //CELL Children group
                                    new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_SUBGROUP(), new GRUPPattern(
                                        [
                                            //persistent children, visible distant children, temp children
                                            new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_SUBGROUP(), new GRUPPattern(
                                                [
                                                    new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_RECORD(),null)
                                                ]
                                            ))
                                        ]
                                    ))
                                 ]
                             ))
                          ]
                      ))
                  ]
              )
            ];
        }

        if(self::$defaultPattern === null)
        {
            self::$defaultPattern = new GRUPPattern(
                [
                    new GRUPPatternRecord(GRUPPatternPossibility::PATTERN_RECORD(),null)
                ]
            );
        }
    }

    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param $handle
     * @param GRUPPattern $pattern
     * @return \Traversable
     * @throws InvalidESFileException
     */
    public function load($handle, GRUPPattern $pattern = null) : \Traversable
    {
        $curpos = ftell($handle);
        $header = fread($handle, self::GRUP_HEADER_SIZE);
        if(substr($header,0,4) != "GRUP") {
            throw new InvalidESFileException("Invalid GRUP magic, found ".substr($header,0,4));
        }

        $this->size = current(unpack("V", substr($header,4,4)));
        $this->type = substr($header,8,4);
        echo 'load grup type '.$this->type.PHP_EOL;

        $end = $curpos + $this->size; //Size includes the header
        echo $end.PHP_EOL;
        if($pattern === null) {
            $pattern = (isset(self::$customPatterns[$this->type])) ? self::$customPatterns[$this->type] : self::$defaultPattern;
            echo 'chosen pattern'.PHP_EOL;
            $fl = 0;
        } else {
            $fl = 1;
            echo 'provided pattern'.PHP_EOL;
        }

        var_dump($pattern);
        /**
         * @var GRUPPatternRecord $patternRecord
         */
        foreach($pattern as $patternRecord) {

            if(ftell($handle) >= $end) {
                break;
            }

            echo '0x'.dechex(ftell($handle)).PHP_EOL;

            switch($patternRecord->getPossibility()) {
                case GRUPPatternPossibility::PATTERN_RECORD(): {
                    $record = new TES4LoadedRecord();
                    $record->load($handle);
                    echo $record->getType().PHP_EOL;
                    yield $record;
                    break;
                }
                case GRUPPatternPossibility::PATTERN_SUBGROUP(): {
                    $nestedGrup = new TES4Grup();
                    foreach($nestedGrup->load($handle, $patternRecord->getSubpattern()) as $subrecord) {
                        yield $subrecord;
                    }
                }
            }


        }


    }

}