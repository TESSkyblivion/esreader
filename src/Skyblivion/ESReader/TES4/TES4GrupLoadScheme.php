<?php


namespace Skyblivion\ESReader\TES4;


class TES4GrupLoadScheme
{

    private $records = [];

    public function add(TES4RecordType $type, TES4RecordLoadScheme $scheme) : void
    {
        $this->records[$type->value()] = $scheme;
    }

    public function shouldLoad(TES4RecordType $type) : bool
    {
        return isset($this->records[$type->value()]);
    }

    public function getRulesFor(TES4RecordType $type) : ?TES4RecordLoadScheme
    {
        if(!isset($this->records[$type->value()])) {
            return null;
        }

        return $this->records[$type->value()];
    }

}