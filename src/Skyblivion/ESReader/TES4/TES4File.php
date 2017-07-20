<?php

namespace Skyblivion\ESReader\TES4;

use Skyblivion\ESReader\Exception\InvalidESFileException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class TES4File
{
    const TES4_HEADER_SIZE = 0x18;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $masters = [];

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var TES4Grup[]
     */
    private $grups = [];

    /**
     * @var TES4Collection
     */
    private $collection;

    /**
     * File constructor.
     * @param TES4Collection $collection
     * @param string $path
     * @param string $name
     */
    public function __construct(TES4Collection $collection, string $path, string $name)
    {
        $this->collection = $collection;
        $this->path = $path;
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMasters()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->masters;
    }

    public function load(TES4FileLoadScheme $scheme): \Traversable
    {
        $filepath = $this->path . "/" . $this->name;
        $filesize = filesize($filepath);
        $h = fopen($filepath, "rb");
        if (!$h) {
            throw new FileNotFoundException("File " . $filepath . " not found.");
        }

        $this->fetchTES4($h);

        while (ftell($h) < $filesize) {
            $grup = new TES4Grup();

            $header = fread($h, TES4Grup::GRUP_HEADER_SIZE);
            fseek($h, -TES4Grup::GRUP_HEADER_SIZE, SEEK_CUR);

            if (substr($header, 0, 4) != "GRUP") {
                throw new InvalidESFileException("Invalid GRUP magic, found " . substr($header, 0, 4));
            }

            $grupType = TES4RecordType::memberByValue(substr($header, 8, 4));
            $grupSize = current(unpack("V", substr($header, 4, 4)));

            if($scheme->shouldLoad($grupType)) {
                foreach ($grup->load($h, $this, $scheme->getRulesFor($grupType), true) as $loadedRecord) {
                    yield $loadedRecord;
                }
            } else {
                fseek($h, $grupSize, SEEK_CUR);
            }

            $this->grups[$grup->getType()->value()] = $grup;
        }

        fclose($h);

    }

    public function getGrup(TES4RecordType $type): ?\Iterator
    {
        if (!isset($this->grups[$type->value()])) {
            return null;
        }

        return $this->grups[$type->value()]->getIterator();
    }

    public function expand(int $formid): int
    {
        return $this->collection->expand($formid, $this->getName());
    }

    private function fetchTES4($h)
    {
        $recordHeader = fread($h, TES4LoadedRecord::RECORD_HEADER_SIZE);
        $recordSize = current(unpack("V", substr($recordHeader, 4, 4)));
        $recordFormid = current(unpack("V", substr($recordHeader, 0xC, 4)));
        $recordFlags = current(unpack("V", substr($recordHeader, 8, 4)));
        $tes4record = new TES4LoadedRecord($this, TES4RecordType::TES4(), $recordFormid, $recordSize, $recordFlags);

        $tes4record->load($h, new TES4RecordLoadScheme(['MAST']));

        return $tes4record;

    }

    private function initialize()
    {
        $filepath = $this->path . "/" . $this->name;
        $h = fopen($filepath, "rb");
        if (!$h) {
            throw new FileNotFoundException("File " . $filepath . " not found.");
        }

        $tes4record = $this->fetchTES4($h);
        $masters = $tes4record->getSubrecords("MAST");
        $masterIndex = 0;
        foreach ($masters as $master) {
            $this->masters[$masterIndex++] = $master;
        }


        fclose($h);
        $this->initialized = true;
    }

}