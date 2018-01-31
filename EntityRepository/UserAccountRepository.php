<?php

namespace  MemberPoint\WOS\UsersBundle\EntityRepository;

use MemberPoint\WOS\UsersBundle\Entity\UserAccount;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserAccountRepository extends ServiceEntityRepository
{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserAccount::class);
    }


    public function findOneByEmailAddress($emailAddress)
    {
        return parent::findOneBy(['emailAddress' => $emailAddress]);
    }

    public function newUser(UserAccount $userAccount)
    {
        try {
            if (!$this->containsUser($userAccount)){
                $this->_em->persist($userAccount);
                $this->_em->flush();
            }
        } catch (Exception $e) {
            //@TODO Change the catch of exception and the exception handle
            error_log(
                sprintf(
                    '%s::addItem() - %s.',
                    static::CLASS,
                    $e->getMessage()
                )
            );

        }
    }

    public function updateUser(UserAccount $userAccount){
        try {
            if ($this->containsUser($userAccount)){
                $this->_em->persist($userAccount);
                $this->_em->flush();
            }

        } catch (Exception $e) {
            //@TODO Change the catch of exception and the exception handle
            error_log(
                sprintf(
                    '%s::addItem() - %s.',
                    static::CLASS,
                    $e->getMessage()
                )
            );

        }
    }

    public function containsUser(UserAccount $user)
    {
        return $this->_em->contains($user);
    }

}