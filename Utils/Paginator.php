<?php

namespace  MemberPoint\WOS\UsersBundle\Utils;

use Symfony\Component\OptionsResolver\OptionsResolver;

class Paginator implements \JsonSerializable
{
    protected $currentPageNumber;
    protected $hasPagesFollowingOverflow;
    protected $hasPagesPreceedingOverflow;
    protected $nextStartingItemIndex;
    protected $pages;
    protected $previousStartingItemIndex;
    protected $totalPagesCount;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'limitToPagesCount' => -1
            )
        );

        $resolver->setAllowedTypes('limitToPagesCount', 'int');
    }

    public static function newFromPaginatedSet(
        PaginatedSet $set,
        array $options = array()
    ) {
        return new Paginator(
            $set->foundCount(),
            $set->startingIndex(),
            $set->maxCount(),
            $options
        );
    }

    public function __construct(
        $totalItemsCount,
        $currentItemIndex = 0,
        $itemsPerPageCount = 10,
        array $options = array()
    ) {
        $resolver = new OptionsResolver();
        static::configureOptions($resolver);
        $options = $resolver->resolve($options);

        $this->hasPagesFollowingOverflow = false;
        $this->hasPagesPreceedingOverflow = false;
        $this->nextStartingItemIndex = -1;
        $this->pages = array();
        $this->previousStartingItemIndex = -1;
        $this->totalPagesCount = 0;

        $useCurrentItemIndex = (int) $currentItemIndex;
        $useItemsPerPageCount = (int) $itemsPerPageCount;
        $useLimitToPagesCount = (int) $options['limitToPagesCount'];
        $useTotalItemsCount = (int) $totalItemsCount;

        if ($useLimitToPagesCount <= 0) {

            $useLimitToPagesCount = 18446744073709551615;
        }

        $this->totalPagesCount = (int) ceil(
            $useTotalItemsCount / $useItemsPerPageCount
        );

        /*
            Page-numbers are non-zero indexed. So first page is number 1.
        */

        $this->currentPageNumber = (0 == $useCurrentItemIndex)
            ? 1 : (int) floor($useCurrentItemIndex / $useItemsPerPageCount) + 1;

        $endingPageNumber = ($this->currentPageNumber + $useLimitToPagesCount - 1 >= $this->totalPagesCount)
            ? $this->totalPagesCount : ($this->currentPageNumber + $useLimitToPagesCount) - 1;

        $startingPageNumber = ($useLimitToPagesCount >= $endingPageNumber)
            ? 1 : ($endingPageNumber - $useLimitToPagesCount) + 1;

        /*
            Calculate the previous starting-item index.
        */

        if (1 < $this->currentPageNumber) {

            $this->previousStartingItemIndex
                = ($this->currentPageNumber - 2) * $useItemsPerPageCount;
        }

        /*
            Calculate the next starting-item index.
        */

        if ($this->totalPagesCount > $this->currentPageNumber) {

            $this->nextStartingItemIndex = $this->currentPageNumber * $useItemsPerPageCount;
        }

        /*
            Remember page-numbers as non-zero indexed.
        */

        for ($idx = 1; $idx <= $this->totalPagesCount; $idx++) {

            if ($startingPageNumber <= $idx
                && $endingPageNumber >= $idx
            ) {
                $page = $this->doMakePage($idx);
                $page->startingItemIndex = ($idx - 1) * $useItemsPerPageCount;

                $this->pages[] = $page;

            } elseif ($startingPageNumber == $idx + 1) {

                $this->hasPagesPreceedingOverflow = true;

            } elseif ($endingPageNumber == $idx - 1) {

                $this->hasPagesFollowingOverflow = true;

                break;
            }
        }
    }

    public function currentPageNumber()
    {
        return $this->currentPageNumber;
    }

    /**
     * Returns true if the current page has pages following;
     * false otherwise.
     *
     * @return boolean
     */
    public function hasPagesFollowing()
    {
        return (-1 !== $this->nextStartingItemIndex());
    }

    /**
     * Returns true if the current page has pages following that are *not*
     * being returned by Paginator::pages() because of the $limitToPagesCount
     * directive.
     *
     * @return boolean
     */
    public function hasPagesFollowingOverflow()
    {
        return $this->hasPagesFollowingOverflow;
    }

    /**
     * Returns true if the current page has preceeding pages;
     * false otherwise.
     *
     * @return boolean
     */
    public function hasPagesPreceeding()
    {
        return (-1 !== $this->previousStartingItemIndex());
    }

    /**
     * Returns true if the current page has preceeding pages that are *not*
     * being returned by Paginator::pages() because of the $limitToPagesCount
     * directive.
     *
     * @return boolean
     */
    public function hasPagesPreceedingOverflow()
    {
        return $this->hasPagesPreceedingOverflow;
    }

    public function jsonSerialize()
    {
        $obj = new \stdClass();
        $obj->currentPageNumber = $this->currentPageNumber();
        $obj->hasPagesFollowing = $this->hasPagesFollowing();
        $obj->hasPagesFollowingOverflow = $this->hasPagesFollowingOverflow();
        $obj->hasPagesPreceeding = $this->hasPagesPreceeding();
        $obj->hasPagesPreceedingOverflow = $this->hasPagesPreceedingOverflow();
        $obj->nextStartingItemIndex = $this->nextStartingItemIndex();
        $obj->pages = array();
        $obj->pagesCount = $this->pagesCount();
        $obj->previousStartingItemIndex = $this->previousStartingItemIndex();

        $obj->hasNext = $obj->hasPagesFollowing;
        $obj->hasNextOverflow = $obj->hasPagesFollowingOverflow;
        $obj->hasPrevious = $obj->hasPagesPreceeding;
        $obj->hasPreviousOverflow = $obj->hasPagesPreceedingOverflow;

        foreach ($this->pages() as $page) {

            $objPage = new \stdClass();
            $objPage->isCurrent = ($page->number == $obj->currentPageNumber);
            $objPage->number = $page->number;
            $objPage->startingItemIndex = $page->startingItemIndex;

            $obj->pages[] = $objPage;
        }

        return $obj;
    }

    /**
     * Returns the zero-based index of the starting item of the page following
     * the current page; or -1 if there is no page following the current page.
     *
     * @return int
     */
    public function nextStartingItemIndex()
    {
        return $this->nextStartingItemIndex;
    }

    public function pages()
    {
        return $this->pages;
    }

    public function pagesCount()
    {
        return count($this->pages);
    }

    /**
     * Returns the zero-based index of the starting item of the page preceeding
     * the current page; or -1 if there is no page preceeding the current page.
     *
     * @return int
     */
    public function previousStartingItemIndex()
    {
        return $this->previousStartingItemIndex;
    }

    /**
     * Returns the total number of pages used for the pagination. Note, this
     * number may or may not equal the number of pages returned from
     * Paginator::pages() because of the $limitToPagesCount
     * directive.
     *
     * @return integer
     */
    public function totalPagesCount()
    {
        return $this->totalPagesCount;
    }

    protected function doMakePage($pageNumber)
    {
        $page = new \stdClass();
        $page->number = (int) $pageNumber;
        $page->startingItemIndex = 0;

        return $page;
    }
}
