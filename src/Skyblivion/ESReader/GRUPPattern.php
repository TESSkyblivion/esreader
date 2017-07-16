<?php


namespace Skyblivion\ESReader;


class GRUPPattern implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $pattern;

    /**
     * GRUPPattern constructor.
     * @param GRUPPatternRecord[] $pattern
     */
    public function __construct(array $pattern)
    {
        $this->pattern = $pattern;
    }

    public function getIterator()
    {
        return new \InfiniteIterator(new \ArrayIterator($this->pattern));
    }


}