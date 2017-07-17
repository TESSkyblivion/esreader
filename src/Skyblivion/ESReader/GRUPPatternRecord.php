<?php


namespace Skyblivion\ESReader;


/**
 * Class GRUPPatternRecord
 * @package Skyblivion\ESReader
 */
class GRUPPatternRecord
{
    /**
     * @var GRUPPatternPossibility
     */
    private $possibility;

    /**
     * @var GRUPPattern
     */
    private $subpattern;

    /**
     * GRUPPatternRecord constructor.
     * @param GRUPPatternPossibility $possibility
     * @param GRUPPattern $subpattern
     */
    public function __construct(GRUPPatternPossibility $possibility, GRUPPattern $subpattern = null)
    {
        $this->possibility = $possibility;
        $this->subpattern = $subpattern;
    }

    /**
     * @return GRUPPatternPossibility
     */
    public function getPossibility(): GRUPPatternPossibility
    {
        return $this->possibility;
    }

    /**
     * @return GRUPPattern
     */
    public function getSubpattern(): GRUPPattern
    {
        return $this->subpattern;
    }


}