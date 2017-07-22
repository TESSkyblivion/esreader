<?php


namespace Skyblivion\ESReader\Struct;

/**
 * Class Trie
 * Based off implementation of https://github.com/fran6co/phptrie/
 */
class Trie
{
    private $trie = array();
    private $value = null;

    /**
     * Trie constructor
     *
     * @param mixed $value This is for internal use
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * Add value to the trie
     *
     * @param $string string The key
     * @param $value mixed The value
     * @param bool $overWrite Overwrite existing value
     */
    public function add($string, $value, $overWrite=true) : void
    {
        if (empty($string)) {
            if (is_null($this->value) || $overWrite) {
                $this->value = $value;
            }

            return;
        }

        foreach ($this->trie as $prefix => $trie) {
            $prefix = (string)$prefix;
            $prefixLength = strlen($prefix);
            $head = substr($string,0,$prefixLength);
            $headLength = strlen($head);

            $equals = true;
            $equalPrefix = "";
            for ($i= 0;$i<$prefixLength;++$i) {
                //Split
                if ($i >= $headLength) {
                    $equalTrie = new Trie($value);
                    $this->trie[$equalPrefix] = $equalTrie;
                    $equalTrie->trie[substr($prefix,$i)] = $trie;
                    unset($this->trie[$prefix]);

                    return;
                } elseif ($prefix[$i] != $head[$i]) {
                    if ($i > 0) {
                        $equalTrie = new Trie();
                        $this->trie[$equalPrefix] = $equalTrie;
                        $equalTrie->trie[substr($prefix,$i)] = $trie;
                        $equalTrie->trie[substr($string,$i)] = new Trie($value);
                        unset($this->trie[$prefix]);

                        return;
                    }
                    $equals = false;
                    break;
                }

                $equalPrefix .= $head[$i];
            }

            if ($equals) {
                $trie->add(substr($string,$prefixLength),$value,$overWrite);

                return;
            }
        }

        $this->trie[$string] = new Trie($value);
    }

    /**
     * Search the Trie with a string
     *
     * @param $string string The string search
     *
     * @return mixed The value
     */
    public function search($string)
    {
        if (empty($string)) {
            return $this->value;
        }

        foreach ($this->trie as $prefix => $trie) {
            $prefix = (string)$prefix;
            $prefixLength = strlen($prefix);
            $head = substr($string,0,$prefixLength);

            if ($head === $prefix) {
                return $trie->search(substr($string,$prefixLength));
            }
        }

        return null;
    }

    public function searchPrefix($string) : TrieIterator
    {
        if(empty($string)) {
            return new TrieIterator($this);
        }

        $stringLength = strlen($string);
        foreach ($this->trie as $prefix => $trie) {
            $prefix = (string)$prefix;
            $prefixLength = strlen($prefix);
            if($prefixLength > $stringLength) {
                $headPrefix = substr($prefix, 0, $stringLength);
                $stringPrefix = $string;
            } else {
                $headPrefix = $prefix;
                $stringPrefix = substr($string, 0, $prefixLength);
            }

            if ($headPrefix === $stringPrefix) {
                return $trie->searchPrefix(substr($string,$prefixLength));
            }
        }

        return new TrieIterator(null);

    }

    public function value()
    {
        return $this->value;
    }

    /**
     * @return \ArrayIterator
     */
    public function subnodes() : \ArrayIterator
    {
        return new \ArrayIterator($this->trie);
    }

}