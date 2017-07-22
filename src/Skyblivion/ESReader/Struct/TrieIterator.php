<?php


namespace Skyblivion\ESReader\Struct;

/**
 * DFS-based iterator for Trie
 * Class TrieIterator
 * @package Skyblivion\ESReader\Struct
 */
class TrieIterator implements \Iterator
{

    /**
     * @var int
     */
    private $k;

    /**
     * @var Trie
     */
    private $root;

    /**
     * @var Trie
     */
    private $current;

    /**
     * @var \SplStack
     */
    private $stack;

    /**
     * TrieIterator constructor.
     * @param Trie|null $root
     */
    public function __construct(?Trie $root)
    {
        $this->root = $root;
        if(null !== $this->root) {
            $this->rewind();
        }
    }

    /**
     * @return mixed|null
     */
    public function current()
    {
        if(null !== $this->current) {
            return $this->current->value();
        }

        return null;
    }

    public function next()
    {
        /**
         * Expand the current node to children
         * @var Trie $subnode
         */
        foreach($this->current->subnodes() as $subnode)
        {
            $this->pushNodeForIteration($subnode);
        }

        $this->popNodeForIteration();
        $this->k++;
    }

    public function key()
    {
        return $this->k;
    }

    public function valid()
    {
        return $this->current !== null;
    }

    public function rewind()
    {
        $this->k = 0;
        $this->stack = new \SplStack();
        if(null !== $this->root) {
            $this->pushNodeForIteration($this->root);
            $this->popNodeForIteration();
        }
    }

    private function pushNodeForIteration(Trie $trie)
    {
        /**
         * There can be intermediary nodes that weren't directly inserted
         * They won't have a value, so let's skip them
         * @var \ArrayIterator[] $nodesToTravel
         */
        $nodesToTravel = [new \ArrayIterator([$trie])];
        while(!empty($nodesToTravel)) {
            /**
             * @var Trie[] $currentNodes
             */
            $currentNodes = array_pop($nodesToTravel);

            foreach($currentNodes as $currentNode)
            {
                if(null !== $currentNode->value()) {
                    $this->stack->push($currentNode);
                } else {
                    $nodesToTravel[] = $currentNode->subnodes();
                }
            }

        }
    }

    private function popNodeForIteration()
    {
        /**
         * Pop the next node
         */
        if($this->stack->count()) {
            $this->current = $this->stack->pop();
        } else {
            $this->current = null;
        }
    }

}