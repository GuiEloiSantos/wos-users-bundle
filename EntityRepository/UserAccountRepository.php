<?php

namespace MemberPoint\WOS\UsersBundle\EntityRepository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use MemberPoint\WOS\UsersBundle\Entity\UserAccount;
use MemberPoint\WOS\UsersBundle\Exception\InvalidRepositoryException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class UserAccountRepository extends ServiceEntityRepository
{

    private static $userAccountRepository;

    /**
     * UserAccountRepository constructor.
     * @param RegistryInterface $registry
     */
    private function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserAccount::class);
    }

    /**
     * @param RegistryInterface $registry
     * @return UserAccountRepository
     */
    public static function setUserAccountRepository(RegistryInterface $registry)
    {
        if (!(self::$userAccountRepository instanceof UserAccountRepository))
            self::$userAccountRepository = new UserAccountRepository($registry);
        return self::getUserAccountRepository();
    }

    /**
     * @return UserAccountRepository
     */
    public static function getUserAccountRepository()
    {
        if(!self::$userAccountRepository instanceof UserAccountRepository){
            throw new InvalidRepositoryException(__CLASS__);
        }
        return self::$userAccountRepository;
    }
    /**
     * @param $emailAddress
     * @return null|object
     */
    public function findOneByEmailAddress($emailAddress)
    {
        return parent::findOneBy(['emailAddress' => $emailAddress]);
    }

    /**
     * @param $id
     * @return null|object
     */
    public function findOneById($id)
    {
        return parent::find($id);
    }

    /**
     * @param UserAccount $userAccount
     */
    public function newUser(UserAccount $userAccount)
    {
        try {
            if (!$this->containsUser($userAccount)) {
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

    /**
     * @param UserAccount $user
     * @return bool
     */
    public function containsUser(UserAccount $user)
    {
        return $this->_em->contains($user);
    }

    /**
     * @param UserAccount $userAccount
     */
    public function updateUser(UserAccount $userAccount)
    {
        try {
            if ($this->containsUser($userAccount)) {
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

}