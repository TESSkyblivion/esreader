<?php


namespace Skyblivion\ESReader\TES4;


class TES4RecordLoadScheme
{

    private $subrecords = [];

    public function __construct($subrecords)
    {
        foreach($subrecords as $subrecord)
        {
            $this->subrecords[$subrecord] = true;
        }
    }

    public function shouldLoad($subrecord)
    {
        return isset($this->subrecords[$subrecord]);
    }


}