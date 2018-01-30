<?php


namespace  MemberPoint\WOS\UsersBundle\Controller;

use MemberPoint\WOS\UsersBundle\Entity\UserAccount;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserAccountController extends Controller{


    public function index(){
        $em = $this->getDoctrine()->getManager();

        $userAccount = new UserAccount();
        $userAccount->firstName = "Gui";
        $userAccount->lastLoginAttemptDttm = "Gui";
        $userAccount->emailAddress = "Gui@gmail.com";
        $userAccount->password = "dotatat12";
        $em->persist($userAccount);
    }
}