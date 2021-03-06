<?php

namespace Skyblivion\ESReader\TES4;

class TES4LoadedRecord implements TES4Record
{
    const RECORD_HEADER_SIZE = 20;

    /**
     * @var TES4File
     */
    private $placedFile;

    /**
     * @var int;
     */
    private $formid;

    /**
     * @var int;
     */
    private $expandedFormid;

    /**
     * @var int
     */
    private $flags;

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

    /**
     * @var array
     */
    private $dataAsFormidCache = [];

    /**
     * TES4LoadedRecord constructor.
     * @param TES4File $placedFile
     * @param TES4RecordType $type
     * @param int $formid
     * @param int $size
     * @param int $flags
     */
    public function __construct(TES4File $placedFile, TES4RecordType $type, int $formid, int $size, int $flags)
    {
        $this->placedFile = $placedFile;
        $this->type = $type;
        $this->formid = $formid;
        $this->size = $size;
        $this->flags = $flags;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubrecords(string $type): array
    {
        if (!isset($this->data[$type])) {
            return [];
        }

        return $this->data[$type];
    }

    public function getSubrecord(string $type): ?string
    {
        if (!isset($this->data[$type]) || !isset($this->data[$type][0])) {
            return null;
        }

        return $this->data[$type][0];
    }

    public function getSubrecordAsFormid(string $type): ?int
    {
        if (!isset($this->dataAsFormidCache[$type])) {
            $subrecord = $this->getSubrecord($type);
            if (null === $subrecord) {
                return null;
            }

            if (strlen($subrecord) < 4) {
                return null;
            }

            $this->dataAsFormidCache[$type] = $this->placedFile->expand(current(unpack('V', substr($subrecord, 0, 4))));
        }

        return $this->dataAsFormidCache[$type];
    }


    public function getFormId(): int
    {
        if ($this->expandedFormid === null) {
            $this->expandedFormid = $this->placedFile->expand($this->formid);
        }
        return $this->expandedFormid;
    }

    public function load($handle, TES4RecordLoadScheme $scheme): void
    {
        if($this->size == 0) {
            return;
        }

        $data = fread($handle, $this->size);

        //Decompression
        if ($this->flags & 0x00040000) {
            //Skip the uncompressed data size
            $this->size = current(unpack('V', substr($data, 0, 4)));
            $data = substr($data, 4);
            $data = gzuncompress($data);
        }

        $i = 0;

        while ($i < $this->size) {
            $subrecordType = substr($data, $i, 4);
            $subrecordSize = current(unpack("v", substr($data, $i + 4, 2)));

            if($scheme->shouldLoad($subrecordType)) {
                $subrecordData = substr($data, $i + 6, $subrecordSize);

                if (!isset($this->data[$subrecordType])) {
                    $this->data[$subrecordType] = [];
                }

                $this->data[$subrecordType][] = $subrecordData;
            }

            $i += ($subrecordSize + 6);
        }

    }

}