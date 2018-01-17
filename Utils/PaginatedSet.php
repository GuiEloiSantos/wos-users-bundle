<?php

namespace  MemberPoint\WOS\UsersBundle\Utils;

class PaginatedSet implements \Countable, \Iterator
{
    protected $endingIndex;
    protected $foundCount;
    protected $items;
    protected $maxCount;
    protected $startingIndex;

    private $cursor = 0;

    public function __construct(
        array $items,
        $foundCount,
        $startingIndex = 0,
        $maxCount = null
    ) {
        $this->foundCount = (int) $foundCount;
        $this->items = array();
        $this->maxCount = (int) $maxCount;
        $this->startingIndex = (int) $startingIndex;

        $this->setItems($items);
    }

    public function count()
    {
        return $this->itemsCount();
    }

    public function current()
    {
        return $this->getItem($this->cursor);
    }

    public function endingIndex()
    {
        return $this->endingIndex;
    }

    public function foundCount()
    {
        return $this->foundCount;
    }

    public function getItem($index)
    {
        $index = (int) $index;

        if (array_key_exists($index, $this->items)) {

            return $this->items[$index];

        } else {

            throw new \OutOfBoundsException();
        }
    }

    public function items()
    {
        return $this->items;
    }

    public function itemsCount()
    {
        return count($this->items);
    }

    public function key()
    {
        return $this->cursor;
    }

    public function maxCount()
    {
        return $this->maxCount;
    }

    public function next()
    {
        $this->cursor++;
    }

    public function rewind()
    {
        $this->cursor = 0;
    }

    public function setItem($index, $item)
    {
        $index = (int) $index;

        if (array_key_exists($index, $this->items)) {

            $this->items[$index] = $item;

        } else {

            throw new \OutOfBoundsException();
        }
    }

    public function startingIndex()
    {
        return $this->startingIndex;
    }

    public function valid()
    {
        return $this->cursor < count($this->items);
    }

    protected function setItems(array $items)
    {
        foreach ($items as $idx => $item) {

            $this->items[] = $item;

            $this->endingIndex = $idx;
        }
    }
}
