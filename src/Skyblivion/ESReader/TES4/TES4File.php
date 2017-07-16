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

    private $initialized = false;

    /**
     * File constructor.
     * @param string $path
     * @param string $name
     */
    public function __construct(string $path, string $name)
    {
        $this->path = $path;
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getMasters()
    {
        if(!$this->initialized) {
            $this->initialize();
        }

        return $this->masters;
    }

    public function load() : \Traversable {
        $filepath = $this->path . "/" . $this->name;
        $filesize = filesize($filepath);
        $h = fopen($filepath,"rb");
        if(!$h) {
            throw new FileNotFoundException("File " . $filepath . " not found.");
        }

        $this->fetchTES4($h);

        while(ftell($h) < $filesize) {
            echo 'load grup'.PHP_EOL;
            $grup = new TES4Grup();
            foreach($grup->load($h) as $loadedRecord) {
                yield $loadedRecord;
            }
        }

        fclose($h);

    }

    private function fetchTES4($h) {
        echo 'fetch tes4'.PHP_EOL;
        $tes4record = new TES4LoadedRecord();
        $tes4record->load($h);

        if($tes4record->getType() != "TES4") {
            throw new InvalidESFileException("Invalid magic.");
        }

        return $tes4record;

    }

    private function initialize() {
        $filepath = $this->path . "/" . $this->name;
        $h = fopen($filepath,"rb");
        if(!$h) {
            throw new FileNotFoundException("File " . $filepath . " not found.");
        }

        $tes4record = $this->fetchTES4($h);
        $masters = $tes4record->getSubrecords("MAST");
        $masterIndex = 0;
        foreach($masters as $master)
        {
            $this->masters[$masterIndex++] = $master;
        }


        fclose($h);
        $this->initialized = true;
    }

}