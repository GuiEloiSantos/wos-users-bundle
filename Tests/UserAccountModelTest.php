<?php
/**
 * Created by PhpStorm.
 * User: gcunh
 * Date: 1/16/2018
 * Time: 3:21 PM
 */

namespace MemberPoint\WOS\UsersBundle\tests;

use Memberpoint\WOS\UsersBundle\Model\UserAccountModel as UserAccountModel ;
use PHPUnit\Framework\TestCase;

class UserAccountModelTest extends TestCase
{
    public function testPassword(){
        new UserAccountModel();
        $this->assertEquals(false, UserAccountModel::setPassword("23244"));
        $this->assertEquals(false, UserAccountModel::setPassword("guizin"));
        $this->assertEquals(false, UserAccountModel::setPassword("guizin!!@#!"));
        $this->assertEquals(true, UserAccountModel::setPassword("guizin12"));
    }

}
