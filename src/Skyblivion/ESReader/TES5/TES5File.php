<?php

namespace Skyblivion\ESReader;


use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class TES5File
{

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $name;

    private $masters;

    private $groups;

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

    public function load(): void
    {
        $filepath = $this->path . "/" . $this->name;
        $h = fopen($filepath, "rb");
        if (!$h) {
            throw new FileNotFoundException("File " . $filepath . " not found.");
        }


    }

}