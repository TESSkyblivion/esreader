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

    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param $handle
     * @param TES4File $file
     * @return \Traversable
     * @throws InvalidESFileException
     */
    public function load($handle, TES4File $file): \Traversable
    {
        $curpos = ftell($handle);
        $header = fread($handle, self::GRUP_HEADER_SIZE);
        if (substr($header, 0, 4) != "GRUP") {
            throw new InvalidESFileException("Invalid GRUP magic, found " . substr($header, 0, 4));
        }

        $this->size = current(unpack("V", substr($header, 4, 4)));
        $this->type = substr($header, 8, 4);

        $end = $curpos + $this->size; //Size includes the header
        /**
         * @var GRUPPatternRecord $patternRecord
         */
        while (ftell($handle) < $end) {

            //Ineffective lookahead, but oh well, fuck it
            $nextEntryType = fread($handle, 4);
            fseek($handle, -4, SEEK_CUR);

            switch ($nextEntryType) {
                case 'GRUP': {
                    $nestedGrup = new TES4Grup();
                    foreach ($nestedGrup->load($handle, $file) as $subrecord) {
                        yield $subrecord;
                    }
                    break;
                }
                default: {
                    $record = new TES4LoadedRecord($file);
                    $record->load($handle);
                    yield $record;
                    break;
                }
            }


        }

    }

}