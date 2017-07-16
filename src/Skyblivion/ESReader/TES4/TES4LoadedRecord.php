<?php

namespace Skyblivion\ESReader\TES4;


use Skyblivion\ESReader\Exception\InvalidESFileException;

class TES4LoadedRecord implements TES4Record
{
    const RECORD_HEADER_SIZE = 20;

    /**
     * @var int;
     */
    private $formid;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $data = [];

    public function getType() : string
    {
        return $this->type;
    }

    public function getSubrecords(string $type) : array {
        if(!isset($this->data[$type])) {
            return [];
        }

        return $this->data[$type];
    }

    public function getSubrecord(string $type) : ?string
    {
        if(!isset($this->data[$type]) || !isset($this->data[$type][0])) {
            return null;
        }

        return $this->data[$type][0];
    }

    public function getFormId() : int {
        return $this->formid;
    }

    public function load($handle) : void
    {
        $header = fread($handle, self::RECORD_HEADER_SIZE);
        $this->type = substr($header,0,4);
        $this->size = current(unpack("V", substr($header,4,4)));
        $this->formid = current(unpack("V", substr($header, 0xC, 4)));
        $flags = current(unpack("V", substr($header,8, 4)));

        if($this->size == 0) {
            return;
        }

        $data = fread($handle,$this->size);

        //Decompression
        if($flags & 0x00040000) {
            //Skip the uncompressed data size
            $this->size = current(unpack('V',substr($data,0,4)));
            $data = substr($data, 4);
            $data = gzuncompress($data);
        }

        $i = 0;

        while($i < $this->size) {
            $subrecordType = substr($data, $i, 4);
            $subrecordSize = current(unpack("v", substr($data, $i+4, 2)));
            $subrecordData = substr($data, $i+6, $subrecordSize);

            if(!isset($this->data[$subrecordType])) {
                $this->data[$subrecordType] = [];
            }

            $this->data[$subrecordType][] = $subrecordData;

            $i += ($subrecordSize + 6);
        }

    }

}