<?php


namespace Skyblivion\ESReader\TES4;


interface TES4Record
{

    public function getFormId(): int;

    public function getType(): string;

    public function getSubrecordAsFormid(string $type): ?int;

    public function getSubrecord(string $type): ?string;

    public function getSubrecords(string $type): array;
}