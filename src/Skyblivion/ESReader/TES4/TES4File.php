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

    public function load(): \Traversable
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
            foreach ($grup->load($h, $this) as $loadedRecord) {
                yield $loadedRecord;
            }
            $this->grups[$grup->getType()] = $grup;
        }

        fclose($h);

    }

    public function getGrup(string $type): ?\Iterator
    {
        if (!isset($this->grups[$type])) {
            return null;
        }

        return new \ArrayIterator($this->grups[$type]);
    }

    public function expand(int $formid): int
    {
        return $this->collection->expand($formid, $this->getName());
    }

    private function fetchTES4($h)
    {
        $tes4record = new TES4LoadedRecord($this);
        $tes4record->load($h);

        if ($tes4record->getType() != "TES4") {
            throw new InvalidESFileException("Invalid magic.");
        }

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