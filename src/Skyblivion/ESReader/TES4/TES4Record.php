<?php


namespace Skyblivion\ESReader\TES4;


interface TES4Record
{

    /**
     * Get collapsed formid
     */
    public function getFormId(): int;

    public function getType(): string;

    public function getSubrecord(string $type): ?string;

    public function getSubrecords(string $type): array;
}