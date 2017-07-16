<?php

namespace Skyblivion\ESReader;

class TES5Collection {

    private $path;

    private $lastIndex = 0;

    /**
     * @var TES4File[]
     */
    private $files = [];

    /**
     * TES5Collection constructor.
     * @param $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function add($name) : void
    {
        $this->files[$this->lastIndex++] = new TES4File($this->path, $name);
    }

    public function load() : void
    {
        foreach($this->files as $file)
        {
            $file->load();
        }
    }

}