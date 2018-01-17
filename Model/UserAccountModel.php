<?php

namespace Memberpoint\WOS\UsersBundle\Model;

use MemberPoint\WOS\UsersBundle\Entity\UserAccount;
use MemberPoint\WOS\UsersBundle\EntityRepository\UserAccountsRepository;
use MemberPoint\WOS\UsersBundle\Utils\Password;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserAccountModel implements \JsonSerializable
{
    public $hasUnsavedChanges;

    protected $usersRepository;
    protected $userEntity;
    protected $validator;

    public function __construct( UserAccount $userEntity )
    {
        $this->userEntity = $userEntity;
    }

    public static function configureDependencies(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'userAccountsRepository' => null,
                'userAccountEntity' => null,
                'validator' => null
            )
        );

        $resolver->setAllowedTypes('usersRepository', 'SavageBull\BA\UsersBundle\EntityRepository\UserAccountsRepository');
        $resolver->setAllowedTypes('userEntity', array('null', 'SavageBull\BA\UsersBundle\Entity\UserAccountEntity'));
        $resolver->setAllowedTypes('validator', 'Symfony\Component\Validator\Validator\ValidatorInterface');

        $resolver->setRequired('usersRepository');
        $resolver->setRequired('validator');
    }

    public function setPassword($password)
    {
        $passwordValidator = new Password(
            array(
                'minLength' => 8,
                'maxLength' => 16,
                'minNumbers' => 1,
                'minLetters' => 1,
                'maxSymbols' => 0
            )
        );
        if ($passwordValidator->validatePassword($password)) {
           $this->userEntity->password = Password::encrypt($password);
           $this->hasUnsavedChanges = true;
            return true;
        } else {
            //@TODO Trow exception
            return false;
        }
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $obj = new \stdClass();
        $obj->id = $this->getId();
        $obj->firstName = $this->getFirstName();
        $obj->lastName = $this->getLastName();
        $obj->emailAddress = $this->getEmailAddress();
        $obj->password = $this->getPassword();
        $obj->nationalId = $this->getNationalId();
        $obj->mobilePhoneNumber = $this->getMobilePhoneNumber();
        $obj->createdByUserHandle = $this->getCreatedByUserHandle();
        $obj->createdByUserAccount = $this->getCreatedByUserAccount();
        $obj->createdDttm = $this->getCreatedDttm();
        $obj->lastModifiedByUserHandle = $this->getLastModifiedByUserHandle();
        $obj->lastModifiedByUserAccount = $this->getLastModifiedByUserAccount();
        $obj->lastModifiedDttm = $this->getLastModifiedDttm();
        $obj->accountVerified = $this->getAccountVerified();
        $obj->tokenExpireDttm = $this->getTokenExpireDttm();
        $obj->lastLoginAttemptDttm = $this->getLastLoginAttemptDttm();
        $obj->countLoginAttemptFailed = $this->getCountLoginAttemptFailed();

        return $obj;
    }

    public function getId()
    {
        return $this->userEntity->id;
    }

    public function getFirstName()
    {
        return $this->userEntity->firstName;
    }

    public function getLastName()
    {
        return $this->userEntity->lastName;
    }

    public function getEmailAddress()
    {
        return $this->userEntity->emailAddress;
    }

    public function getPassword()
    {
        return $this->userEntity->password;
    }

    public function getNationalId()
    {
        return $this->userEntity->nationalId;
    }

    public function getMobilePhoneNumber()
    {
        return $this->userEntity->mobilePhoneNumber;
    }

    public function getCreatedByUserHandle()
    {
        return $this->userEntity->createdByUserHandle;
    }

    public function getCreatedByUserAccount()
    {
        return $this->userEntity->createdByUserAccount;
    }

    public function getCreatedDttm()
    {
        return $this->userEntity->createdDttm;
    }

    public function getLastModifiedByUserHandle()
    {
        return $this->userEntity->lastModifiedByUserHandle;
    }

    public function getLastModifiedByUserAccount()
    {
        return $this->userEntity->lastModifiedByUserAccount;
    }

    public function getLastModifiedDttm()
    {
        return $this->userEntity->lastModifiedDttm;
    }

    public function getAccountVerified()
    {
        return $this->userEntity->accountVerified;
    }

    public function getTokenExpireDttm()
    {
        return $this->userEntity->tokenExpireDttm;
    }

    public function getLastLoginAttemptDttm()
    {
        return $this->userEntity->lastLoginAttemptDttm;
    }

    public function getCountLoginAttemptFailed()
    {
        return $this->userEntity->countLoginAttemptFailed;
    }
}