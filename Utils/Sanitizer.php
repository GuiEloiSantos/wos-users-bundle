<?php

namespace  MemberPoint\WOS\UsersBundle\Utils;

use Symfony\Component\OptionsResolver\OptionsResolver;

class Sanitizer
{
    const DTYPE_ARRAY = 10;
    const DTYPE_BOOL = 20;
    const DTYPE_DATETIME = 30;
    const DTYPE_EMAIL_ADDRESS = 61;
    const DTYPE_FLOAT = 40;
    const DTYPE_INT = 50;
    const DTYPE_STRING = 60;

    const DTTM_RANGE_SPECIFIER_LAST24HOURS = 'last24hours';
    const DTTM_RANGE_SPECIFIER_LAST7DAYS = 'last7days';
    const DTTM_RANGE_SPECIFIER_TODAY = 'today';
    const DTTM_RANGE_SPECIFIER_YESTERDAY = 'yesterday';

    public static function configureSanitizeArrayOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'clearNulls' => false,
                'preserveEmpty' => false,
                'sanitizeItemsAs' => null
            )
        );

        $resolver->setAllowedTypes('clearNulls', 'boolean');
        $resolver->setAllowedTypes('preserveEmpty', 'boolean');
        $resolver->setAllowedTypes('sanitizeItemsAs', array('null', 'integer'));

        $resolver->setAllowedValues(
            'sanitizeItemsAs',
            array(
                self::DTYPE_ARRAY,
                self::DTYPE_BOOL,
                self::DTYPE_EMAIL_ADDRESS,
                self::DTYPE_FLOAT,
                self::DTYPE_INT,
                self::DTYPE_STRING
            )
        );
    }

    public static function configureSanitizeBooleanOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'defaultTo' => null
            )
        );

        $resolver->setAllowedTypes('defaultTo', array('null', 'boolean'));
    }

    public static function configureSanitizeFloatOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'makeAbsolute' => false,
                'preserveZero' => false
            )
        );

        $resolver->setAllowedTypes('makeAbsolute', 'boolean');
        $resolver->setAllowedTypes('preserveZero', 'boolean');
    }

    public static function configureSanitizeIntegerOptions(OptionsResolver $resolver)
    {
        static::configureSanitizeFloatOptions($resolver);
    }

    public static function configureSanitizeStringOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'compact' => true,
                'maxLength' => null,
                'preserveEmpty' => false,
                'trim' => true,
                'xref' => null
            )
        );

        $resolver->setAllowedTypes('compact', 'boolean');
        $resolver->setAllowedTypes('maxLength', array('null', 'int'));
        $resolver->setAllowedTypes('preserveEmpty', 'boolean');
        $resolver->setAllowedTypes('trim', 'boolean');
        $resolver->setAllowedTypes('xref', array('null', 'string', 'array'));
    }

    public static function sanitizeArray($array, array $options = array())
    {
        $resolver = new OptionsResolver();
        static::configureSanitizeArrayOptions($resolver);
        $options = $resolver->resolve($options);

        if (!is_array($array)) {

            $staged = array();

        } else {

            $staged = $array;
        }

        if (!is_null($options['sanitizeItemsAs'])) {

            switch ($options['sanitizeItemsAs']) {

                case self::DTYPE_ARRAY:

                    $fn = 'sanitizeArray';

                    break;

                case self::DTYPE_BOOL:

                    $fn = 'sanitizeBoolean';

                    break;

                case self::DTYPE_EMAIL_ADDRESS:

                    $fn = 'sanitizeEmailAddress';

                    break;

                case self::DTYPE_FLOAT:

                    $fn = 'sanitizeFloat';

                    break;

                case self::DTYPE_INT:

                    $fn = 'sanitizeInteger';

                    break;

                case self::DTYPE_STRING:

                    $fn = 'sanitizeString';

                    break;
            }

            foreach ($staged as $key => $item) {

                $staged[$key] = static::$fn($item);
            }
        }

        if ($options['clearNulls']) {

            $fn = function ($i) {

                return !is_null($i);
            };

            $staged = array_filter($staged, $fn);
        }

        if (empty($staged)
            && !$options['preserveEmpty']
        ) {
            $staged = null;
        }

        return $staged;
    }

    public static function sanitizeBoolean($bool, array $options = array())
    {
        $resolver = new OptionsResolver();
        static::configureSanitizeBooleanOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_bool($bool)) {

            $staged = (bool) $bool;

        } elseif (is_int($bool)) {

            switch ($bool) {

                case 0:

                    $staged = false;

                    break;

                case 1:

                    $staged = true;

                    break;

                default:

                    $staged = null;

                    break;
            }

        } elseif (is_string($bool)) {

            if (0 == strcmp('0', $bool)
                || 0 == strcmp('false', $bool)
            ) {
                $staged = false;

            } elseif (0 == strcmp('1', $bool)
                || 0 == strcmp('true', $bool)
            ) {
                $staged = true;

            } else {

                $staged = null;
            }

        } else {

            $staged = null;
        }

        if (is_null($staged)) {

            $staged = $options['defaultTo'];
        }

        return $staged;
    }

    public static function sanitizeDateTime($dttm)
    {
        $staged = null;

        if ($dttm instanceof \DateTime) {

            $staged = $dttm;

        } elseif (is_string($dttm)) {

            try {

                $staged = static::doMakeDateTime(
                    $dttm,
                    new \DateTimeZone('UTC')
                );

            } catch (\Exception $e) {

                trigger_error('Sanitizer::sanitizeDateTime: unable to create'
                    . ' an instance of DateTime from "' . $dttm . '" - the following'
                    . ' exception was thrown: ' . $e->getMessage(), E_USER_NOTICE);
            }

        } elseif (is_array($dttm)) {

            $tmp = static::sanitizeArray(
                $dttm,
                array(
                    'clearNulls' => true,
                    'preserveEmpty' => true,
                    'sanitizeItemsAs' => self::DTYPE_INT
                )
            );

            $day = 0;
            $hour = 0;
            $minute = 0;
            $month = 0;
            $year = 0;

            extract($tmp, EXTR_IF_EXISTS);

            if (0 < $day
                && 0 < $month
                && 0 < $year
            ) {
                try {

                    $staged = static::doMakeDateTime(
                        strftime(
                            '%c',
                            mktime(
                                $hour,
                                $minute,
                                0,
                                $month,
                                $day,
                                $year
                            )
                        ),
                        new \DateTimeZone('UTC')
                    );

                } catch (\Exception $e) {

                    trigger_error('Sanitizer::sanitizeDateTime: unable to create'
                        . ' an instance of DateTime from distinct parts - the following'
                        . ' exception was thrown: ' . $e->getMessage(), E_USER_NOTICE);
                }
            }
        }

        return $staged;
    }

    public static function sanitizeDateTimeRange(
        $dttmFrom,
        $dttmTo = null
    ) {
        $resulting_dttmFrom = null;
        $resulting_dttmTo = null;
        $effectiveSpecifier = null;

        if (is_string($dttmFrom)) {

            $specifier = static::sanitizeString(strtolower($dttmFrom));

            switch ($specifier) {

                case self::DTTM_RANGE_SPECIFIER_LAST24HOURS:

                    $effectiveSpecifier = $specifier;

                    $resulting_dttmFrom = static::doMakeDateTime(
                        null,
                        new \DateTimeZone('UTC')
                    );

                    $resulting_dttmFrom->sub(
                        new \DateInterval('PT24H')
                    );

                    $resulting_dttmTo = static::doMakeDateTime(
                        null,
                        new \DateTimeZone('UTC')
                    );

                    break;

                case self::DTTM_RANGE_SPECIFIER_LAST7DAYS:
                case self::DTTM_RANGE_SPECIFIER_TODAY:
                case self::DTTM_RANGE_SPECIFIER_YESTERDAY:

                    $effectiveSpecifier = $specifier;

                    $resulting_dttmFrom = static::doMakeDateTime(
                        null,
                        new \DateTimeZone('UTC')
                    );

                    $resulting_dttmFrom->setTime(0, 0, 0);

                    $resulting_dttmTo = static::doMakeDateTime(
                        null,
                        new \DateTimeZone('UTC')
                    );

                    $resulting_dttmTo->setTime(23, 59, 59);

                    switch ($effectiveSpecifier) {

                        case self::DTTM_RANGE_SPECIFIER_LAST7DAYS:

                            $resulting_dttmFrom->sub(
                                new \DateInterval('P7D')
                            );

                            break;

                        case self::DTTM_RANGE_SPECIFIER_YESTERDAY:

                            $resulting_dttmFrom->sub(
                                new \DateInterval('P1D')
                            );

                            $resulting_dttmTo->sub(
                                new \DateInterval('P1D')
                            );

                            break;
                    }

                    break;

                default:

                    /*
                        The string-value isn't one of our pre-defined range
                        specifiers, so pass the responsiblity of handling the
                        value back to our sanitizeDateTime() function.
                    */

                    $resulting_dttmFrom = static::sanitizeDateTime($dttmFrom);

                    break;
            }

        } else {

            $resulting_dttmFrom = static::sanitizeDateTime($dttmFrom);
        }

        if (! is_null($dttmTo)
            && is_null($resulting_dttmTo)
        ) {
            $resulting_dttmTo = static::sanitizeDateTime($dttmTo);
        }

        if (is_null($effectiveSpecifier)
            && $resulting_dttmFrom instanceof \DateTime
            && $resulting_dttmTo instanceof \DateTime
        ) {
            /*
                Determine what range these dates represent. First,
                we create a couple of test dates to perform
                range comparisons. We set their initial times
                to the 'today' range.
            */

            $checkAgainst_dttmFrom = static::doMakeDateTime(
                null,
                new \DateTimeZone('UTC')
            );

            $checkAgainst_dttmFrom->setTime(0, 0, 0);

            $checkAgainst_dttmTo = static::doMakeDateTime(
                null,
                new \DateTimeZone('UTC')
            );

            $checkAgainst_dttmTo->setTime(23, 59, 59);

            /*
                Check if the supplied dates match the
                'today' range.
            */

            if ($resulting_dttmFrom == $checkAgainst_dttmFrom
                && $resulting_dttmTo == $checkAgainst_dttmTo
            ) {
                /*
                    We have a match. Set to 'today'.
                */

                $effectiveSpecifier
                    = self::DTTM_RANGE_SPECIFIER_TODAY;

            } else {

                /*
                    Okay, the supplied dates don't match the 'today'
                    range. Subtract 1 day from the start of the test range
                    and see if the supplied dates match the 'yesterday'
                    range.
                */

                $checkAgainst_dttmFrom->sub(
                    new \DateInterval('P1D')
                );

                $checkAgainst_dttmTo->sub(
                    new \DateInterval('P1D')
                );

                if ($resulting_dttmFrom == $checkAgainst_dttmFrom
                    && $resulting_dttmTo == $checkAgainst_dttmTo
                ) {
                    /*
                        We have a match. Set to 'yesterday'.
                    */

                    $effectiveSpecifier
                        = self::DTTM_RANGE_SPECIFIER_YESTERDAY;

                } else {

                    /*
                        Okay, the supplied dates don't match the 'yesterday'
                        range. Subtract another 6 days from the start of the
                        test range and see if the supplied dates match the
                        'last-seven-days' range. Make sure to add the one day
                        we subracted from the to-date when testing for 'yesterday'.
                    */

                    $checkAgainst_dttmFrom->sub(
                        new \DateInterval('P6D')
                    );

                    $checkAgainst_dttmTo->add(
                        new \DateInterval('P1D')
                    );

                    if ($resulting_dttmFrom == $checkAgainst_dttmFrom
                        && $resulting_dttmTo == $checkAgainst_dttmTo
                    ) {
                        /*
                            We have a match. Set to 'last-seven-days'.
                        */

                        $effectiveSpecifier
                            = self::DTTM_RANGE_SPECIFIER_LAST7DAYS;
                    }
                }
            }
        }

        $results = array();

        $results['dttmFrom'] = $resulting_dttmFrom;
        $results['dttmTo'] = $resulting_dttmTo;
        $results['effectiveSpecifier'] = $effectiveSpecifier;

        return $results;
    }

    public static function sanitizeEmailAddress($address)
    {
        $staged = static::sanitizeString(
            $address,
            array(
                'maxLength' => 320,
                'preserveEmpty' => true
            )
        );

        if (1 !== preg_match('/^[^@\s]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $staged)) {

            $staged = null;
        }

        return $staged;
    }

    public static function sanitizeFloat(
        $num,
        array $options = array()
    ) {
        $resolver = new OptionsResolver();
        static::configureSanitizeFloatOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_numeric($num)) {

            try {

                $staged = (float) $num;

            } catch (\Exception $e) {

                $staged = 0.0;
            }

        } else {

            $staged = 0.0;
        }

        if (0 == $staged
            && !$options['preserveZero']
        ) {
            $staged = null;

        } else {

            if ($options['makeAbsolute']) {

                $staged = abs($staged);
            }
        }

        return $staged;
    }

    public static function sanitizeInteger(
        $num,
        array $options = array()
    ) {
        $staged = static::sanitizeFloat($num, $options);

        if (!is_null($staged)) {

            $staged = (int) $staged;
        }

        return $staged;
    }

    public static function sanitizeString(
        $str,
        array $options = array()
    ) {
        $resolver = new OptionsResolver();
        static::configureSanitizeStringOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_string($str)) {

            $staged = $str;

        } else {

            $staged = '';
        }

        if (!empty($staged)) {

            if ($options['compact']) {

                $staged = preg_replace('/\s+/', ' ', $staged);
            }

            if ($options['trim']) {

                $staged = trim($staged);
            }

            if (!is_null($options['maxLength'])
                && 0 < $options['maxLength']
            ) {
                $staged = substr($staged, 0, $options['maxLength']);
            }

            if (!is_null($options['xref'])) {

                if (is_string($options['xref'])) {

                    if (0 != strcmp($options['xref'], $staged)) {

                        $staged = '';
                    }

                } elseif (is_array($options['xref'])) {

                    $options['xref'] = static::sanitizeArray(
                        $options['xref'],
                        array(
                            'clearNulls' => true,
                            'preserveEmpty' => false,
                            'sanitizeItemsAs' => self::DTYPE_STRING
                        )
                    );

                    $found = false;

                    foreach ($options['xref'] as $str) {

                        if (0 == strcmp($str, $staged)) {

                            $found = true;

                            break;
                        }
                    }

                    if (!$found) {

                        $staged = '';
                    }
                }
            }
        }

        if (empty($staged)
            && !$options['preserveEmpty']
        ) {
            $staged = null;
        }

        return $staged;
    }

    protected static function doMakeDateTime($time = 'now', $timezone = null)
    {
        return new DateTime($time, $timezone);
    }
}
