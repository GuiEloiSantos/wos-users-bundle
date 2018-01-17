<?php

namespace  MemberPoint\WOS\UsersBundle\Utils;

class Fn
{
    public static function each($var, $callback)
    {
        $useVar = $var;

        if (!is_array($useVar)
            && !($useVar instanceof \Traversable)
        ) {
            $useVar = array($useVar);
        }

        foreach ($useVar as $idx => $tmp) {

            call_user_func($callback, $tmp, $idx);
        }
    }

    public static function first($var, $callback = null)
    {
        if (is_array($var)) {

            if (empty($var)) {

                return;
            }

            $useVar = $var[0];

        } elseif ($var instanceof \Traversable
            && $var instanceof \Countable
        ) {
            if (0 == count($var)) {

                return;
            }

            foreach ($var as $tmp) {

                $useVar = $tmp;
                break;
            }

        } else {

            $useVar = $var;
        }

        if (is_null($callback)) {

            return $useVar;
        }

        call_user_func($callback, $useVar);
    }

    /**
     * Helps determine the correct return-type for functions that perform
     * operations equally on both a single value or a collection of values.
     * Typically, a function that accepts a single-value argument will want
     * to return a single value; a function that accepts a collection of values
     * will want to return a collection.
     *
     * This function inspects $basedOn to determine whether to return
     * a single value or a collection of values, and then returns the appropriate
     * type from $returnFrom. Below is a summary of expected behaviour:
     *
     * $basedOn (single-value), $returnFrom (single-value) => $returnFrom
     * $basedOn (single-value), $returnFrom (collection) => $returnFrom[0]
     * $basedOn (single-value), $returnFrom (empty) => null
     * $basedOn (collection), $returnFrom (single-value) => array($returnFrom)
     * $basedOn (collection), $returnFrom (collection) => $returnFrom
     * $basedOn (collection), $returnFrom (empty) => empty array()
     *
     * @param mixed $basedOn
     * @param mixed $returnFrom
     *
     * @return mixed|[]
     */
    public static function singleOrCollection($basedOn, $returnFrom)
    {
        if (!is_array($basedOn)
            && !($basedOn instanceof \Traversable)
        ) {
            if (is_array($returnFrom)) {

                if (empty($returnFrom)) {

                    return null;

                } elseif (1 == count($returnFrom)) {

                    return $returnFrom[0];
                }

            } elseif ($returnFrom instanceof \Traversable
                && $returnFrom instanceof \Countable
            ) {
                if (0 == count($returnFrom)) {

                    return null;

                } elseif (1 == count($returnFrom)) {

                    foreach ($returnFrom as $tmp) {

                        return $tmp;
                    }
                }

            } else {

                throw new \InvalidArgumentException();
            }
        }

        if (empty($returnFrom)) {

            return array();

        } elseif (!is_array($returnFrom)
            && !($returnFrom instanceof \Traversable)
        ) {
            return array($returnFrom);
        }

        return $returnFrom;
    }
}
