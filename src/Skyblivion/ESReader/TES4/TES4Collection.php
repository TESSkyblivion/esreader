<?php

namespace Skyblivion\ESReader\TES4;

use Skyblivion\ESReader\Exception\InconsistentESFilesException;
use Skyblivion\ESReader\Exception\RecordNotFoundException;

class TES4Collection {

    private $path;

    private $lastIndex = 0;

    private $records = [];
    private $edidIndex = [];

    /**
     * @var TES4File[]
     */
    private $files = [];
    private $indexedFiles = [];

    /**
     * @var array
     */
    private $expandTables = [];

    /**
     * TES4Collection constructor.
     * @param $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function add($name) : void
    {
        $file = new TES4File($this->path, $name);
        $this->files[$this->lastIndex++] = $file;
        $this->indexedFiles[$name] = $file;
    }


    public function load() : void
    {
        $this->buildExpandTables();

        foreach($this->files as $index => $file)
        {
            /**
             * @var TES4LoadedRecord $loadedRecord
             */
            foreach($file->load() as $loadedRecord) {
                //no FORMID class encapsulation due to memory budgeting ;)
                $expandedFormid = $this->expand($loadedRecord->getFormId(), $file->getName());
                //TODO resolve conflicts
                $this->records[$expandedFormid] = $loadedRecord;
                $edid = $loadedRecord->getSubrecord('EDID');
                if($edid !== null) {
                    $this->edidIndex[strtolower(trim($edid))] = $loadedRecord;
                }
            }
        }
    }

    public function findByEDID(string $edid) : TES4Record
    {
        $lowerEdid = strtolower($edid);
        if(!isset($this->edidIndex[$lowerEdid])) {
            throw new RecordNotFoundException("EDID ".$edid." not found.");
        }

        return $this->edidIndex[$lowerEdid];
    }

    public function expand(int $formid, string $file)
    {
        if(!isset($this->expandTables[$file])) {
            throw new InconsistentESFilesException("Cannot find file ".$file." in expand table.");
        }

        $index = $formid >> 24;
        if(!isset($this->expandTables[$file][$index])) {
            throw new InconsistentESFilesException("Cannot expand formid index ".$index." in file ".$file);
        }

        return ($this->expandTables[$file][$index] << 24) | ($formid & 0x00FFFFFF);
    }

    private function buildExpandTables()
    {
        //Index
        $fileToIndex = [];
        foreach($this->files as $index => $file) {
            $fileToIndex[$file->getName()] = $index;

        }

        foreach($this->files as $index => $file) {
            $masters = $file->getMasters();
            //Index the file so it can see itself
            //$this->expandTables[$file->getName()] = [count($masters) => $index];
            for($x = 0; $x <= 0xFF; ++$x) {
                $this->expandTables[$file->getName()][$x] = $index;
            }

            foreach($masters as $masterId => $masterName) {
                if(!isset($fileToIndex[$masterName])) {
                    throw new InconsistentESFilesException("File ".$file->getName()." references a master not present in collection.");
                }
                $expandedIndex = $fileToIndex[$masterName];
                $this->expandTables[$file->getName()][$masterId] = $expandedIndex;
            }


        }
    }

}