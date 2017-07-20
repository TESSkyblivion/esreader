<?php

namespace Skyblivion\ESReader\TES4;

use Skyblivion\ESReader\Exception\InvalidESFileException;

/**
 * Represents top level GRUP
 * Class TES4Grup
 * @package Skyblivion\ESReader\TES4
 */
class TES4Grup implements \IteratorAggregate
{
    const GRUP_HEADER_SIZE = 20;

    /**
     * @var int
     */
    private $size;

    /**
     * @var TES4RecordType
     */
    private $type;

    /**
     * @var TES4Record[]
     */
    private $records;

    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return TES4RecordType
     */
    public function getType(): TES4RecordType
    {
        return $this->type;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->records);
    }

    /**
     * @param $handle
     * @param TES4File $file
     * @param TES4GrupLoadScheme $scheme
     * @param bool $isTopLevelGrup
     * @return \Traversable
     * @throws InvalidESFileException
     */
    public function load($handle, TES4File $file, TES4GrupLoadScheme $scheme, $isTopLevelGrup): \Traversable
    {
        $curpos = ftell($handle);
        $header = fread($handle, self::GRUP_HEADER_SIZE);
        if (substr($header, 0, 4) != "GRUP") {
            throw new InvalidESFileException("Invalid GRUP magic, found " . substr($header, 0, 4));
        }

        $this->size = current(unpack("V", substr($header, 4, 4)));

        if ($isTopLevelGrup) {
            $this->type = TES4RecordType::memberByValue(substr($header, 8, 4));
        }

        $end = $curpos + $this->size; //Size includes the header
        while (ftell($handle) < $end) {

            //Ineffective lookahead, but oh well, fuck it
            $nextEntryType = fread($handle, 4);
            fseek($handle, -4, SEEK_CUR);

            switch ($nextEntryType) {
                case 'GRUP': {
                    $nestedGrup = new TES4Grup();
                    foreach ($nestedGrup->load($handle, $file, $scheme, false) as $subrecord) {
                        yield $subrecord;
                    }
                    break;
                }
                default: {

                    $recordHeader = fread($handle, TES4LoadedRecord::RECORD_HEADER_SIZE);
                    $recordType = TES4RecordType::memberByValue(substr($recordHeader, 0, 4));
                    $recordSize = current(unpack("V", substr($recordHeader, 4, 4)));
                    $recordFormid = current(unpack("V", substr($recordHeader, 0xC, 4)));
                    $recordFlags = current(unpack("V", substr($recordHeader, 8, 4)));

                    if($scheme->shouldLoad($recordType)) {
                        $record = new TES4LoadedRecord($file, $recordType, $recordFormid, $recordSize, $recordFlags);
                        $record->load($handle, $scheme->getRulesFor($recordType));
                        $this->records[] = $record;
                        yield $record;
                    } else {
                        fseek($handle, $recordSize, SEEK_CUR);
                    }

                    break;
                }
            }


        }

    }

}