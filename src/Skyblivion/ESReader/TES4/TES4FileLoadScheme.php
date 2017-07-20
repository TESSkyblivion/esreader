<?php


namespace Skyblivion\ESReader\TES4;


class TES4FileLoadScheme
{

    private $grups = [];

    public function add(TES4RecordType $type, TES4GrupLoadScheme $scheme) : void
    {
        $this->grups[$type->value()] = $scheme;
    }

    public function shouldLoad(TES4RecordType $type) : bool
    {
        return isset($this->grups[$type->value()]);
    }

    public function getRulesFor(TES4RecordType $type) : ?TES4GrupLoadScheme
    {
        if(!isset($this->grups[$type->value()])) {
            return null;
        }

        return $this->grups[$type->value()];
    }

}