<?php
/**
 * Created by PhpStorm.
 * User: gcunh
 * Date: 2/1/2018
 * Time: 12:07 PM
 */

namespace MemberPoint\WOS\UsersBundle\Exception;


use Throwable;

class InvalidRepositoryException extends InvalidParameterException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('The repository '.$message.' have never been initialized, initialize it before of using it model/itself', $code, $previous);
    }
}