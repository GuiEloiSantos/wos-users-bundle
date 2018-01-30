<?php

namespace  MemberPoint\WOS\UsersBundle\EntityRepository;

use Doctrine\ORM\EntityRepository;
use MemberPoint\WOS\UsersBundle\Entity\UserAccount;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserAccountRepository extends ServiceEntityRepository
{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserAccount::class);
    }

    public static function configureFindUsersOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(
            array(
                'byId' => null,
                'byName' => null,
                'byEmail' => null,
                'byNationalId' => null,
                'findModifiedBy' => null
            )
        );

    }


    public function newUser(UserAccount $user)
    {
        try {
            if (!$this->containsUser($user))
                $this->_em->persist($user);

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