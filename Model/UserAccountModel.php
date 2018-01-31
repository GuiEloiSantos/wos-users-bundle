<?php

namespace MemberPoint\WOS\UsersBundle\Model;

use MemberPoint\WOS\UsersBundle\Entity\UserAccount;
use MemberPoint\WOS\UsersBundle\Utils\Password;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAccountModel implements \JsonSerializable
{
    protected $hasUnsavedChanges;
    protected $isNewAccount = true;
    protected $userAccountEntity;
    protected $userAccountRepository;
    protected $isAuthenticated = false;

    public function __construct($emailAddress = null, array $deps = array())
    {
        $resolver = new OptionsResolver();
        static::configureDependencies($resolver);
        $deps = $resolver->resolve($deps);
        $this->hasUnsavedChanges = false;
        $this->userAccountRepository = $deps['userAccountRepository'];

        if ($deps['userAccountEntity'])
            $this->userAccountEntity = $deps['userAccountEntity'];
        else {
            $this->userAccountEntity = $this->userAccountRepository->findOneByEmailAddress($emailAddress);
            if (!$this->userAccountEntity) {
                $this->userAccountEntity = new UserAccount();
                $this->setEmailAddress($emailAddress);
            }
        }
        $this->isNewAccount = $this->userAccountRepository->containsUser($this->userAccountEntity) ? true : false;
    }

    public static function configureDependencies(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'userAccountRepository' => null,
                'userAccountEntity' => null
            )
        );

        $resolver->setAllowedTypes('userAccountEntity', array(null, 'MemberPoint\WOS\UsersBundle\Entity\UserAccountEntity'));
        $resolver->setAllowedTypes('userAccountRepository', 'MemberPoint\WOS\UsersBundle\EntityRepository\UserAccountRepository');


        $resolver->setRequired('userAccountRepository');

    }

    public function setEmailAddress($emailAddress)
    {
        if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL) && $this->isNewAccount) {
            $this->userAccountEntity->emailAddress = $emailAddress;
        }
        //@TODO Throw exception invalid email
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
            $this->userAccountEntity->password = Password::encrypt($password);
            $this->hasUnsavedChanges = true;
            return true;
        } else {
            //@TODO Trow exception
            return false;
        }
    }

    public function setFirstName($firstName)
    {
        $this->userAccountEntity->firstName = $firstName;
    }

    public function setLastName($lastName)
    {
        $this->userAccountEntity = $lastName;
    }

    public function setNationalId($nationalId)
    {
        $this->userAccountEntity->nationalId = $nationalId;
    }

    public function setMobilePhoneNumber($mobilePhoneNumber)
    {
        $this->userAccountEntity->mobilePhoneNumber = $mobilePhoneNumber;
    }

    public function setCreatedDttm($createdDttm)
    {
        $this->userAccountEntity->createdDttm = $createdDttm;
    }

    public function setLastModifiedByUserHandle($lastModifiedByUserHandle)
    {
        $this->userAccountEntity->lastModifiedByUserHandle = $lastModifiedByUserHandle;
    }

    public function setLastModifiedDttm($lastModifiedDttm)
    {
        $this->userAccountEntity->lastModifiedDttm = $lastModifiedDttm;
    }

    public function setTokenExpireDttm($tokenExpireDttm)
    {
        $this->userAccountEntity->tokenExpireDttm = $tokenExpireDttm;
    }

    public function setLastLoginAttemptDttm($lastLoginAttemptDttm)
    {
        $this->userAccountEntity->lastLoginAttemptDttm = $lastLoginAttemptDttm;
    }

    public function setCountLoginAttemptFailed($countLoginAttemptFailed)
    {
        $this->userAccountEntity->countLoginAttemptFailed = $countLoginAttemptFailed;
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
        return $this->userAccountEntity->id;
    }

    public function getFirstName()
    {
        return $this->userAccountEntity->firstName;
    }

    public function getLastName()
    {
        return $this->userAccountEntity->lastName;
    }

    public function getEmailAddress()
    {
        return $this->userAccountEntity->emailAddress;
    }

    public function getPassword()
    {
        return $this->userAccountEntity->password;
    }

    public function getNationalId()
    {
        return $this->userAccountEntity->nationalId;
    }

    public function getMobilePhoneNumber()
    {
        return $this->userAccountEntity->mobilePhoneNumber;
    }

    public function getCreatedByUserHandle()
    {
        return $this->userAccountEntity->createdByUserHandle;
    }

    public function getCreatedByUserAccount()
    {
        return $this->userAccountEntity->createdByUserAccount;
    }

    public function getCreatedDttm()
    {
        return $this->userAccountEntity->createdDttm;
    }

    public function getLastModifiedByUserHandle()
    {
        return $this->userAccountEntity->lastModifiedByUserHandle;
    }

    public function getLastModifiedByUserAccount()
    {
        return $this->userAccountEntity->lastModifiedByUserAccount;
    }

    public function getLastModifiedDttm()
    {
        return $this->userAccountEntity->lastModifiedDttm;
    }

    public function getAccountVerified()
    {
        return $this->userAccountEntity->accountVerified;
    }

    public function getTokenExpireDttm()
    {
        return $this->userAccountEntity->tokenExpireDttm;
    }

    public function getLastLoginAttemptDttm()
    {
        return $this->userAccountEntity->lastLoginAttemptDttm;
    }

    public function getCountLoginAttemptFailed()
    {
        return $this->userAccountEntity->countLoginAttemptFailed;
    }

    public function save(UserAccountModel $userAccount)
    {
        if ($this->isNewAccount) {
            $this->setCreatedByUserAccount($userAccount->getId() ? $userAccount->getId() : "");
            $this->setCreatedByUserHandle($userAccount->getEmailAddress() ? $userAccount->getEmailAddress() : "");
            $this->userAccountRepository->newUser($this->userAccountEntity);
            $this->isNewAccount = false;
        } else {
            if ($userAccount->isAuthenticated) {
                $this->setLastModifiedByUserAccount($userAccount->getId());
                $this->setLastModifiedByUserAccount($userAccount->getEmailAddress());
                $this->userAccountRepository->updateUser($this->userAccountEntity);
            }
        }
    }

    public function authenticate($password){
        $this->isAuthenticated = password_verify($password, $this->getPassword());
    }

    public function sendForgetPasswordEmail(){
        if(!$this->isNewAccount){
            //@TODO Send email
        }
    }

    public function setCreatedByUserAccount($createdByUserAccount)
    {
        $this->userAccountEntity->createdByUserAccount = $createdByUserAccount;
    }

    public function setCreatedByUserHandle($createdByUserHandle)
    {
        $this->userAccountEntity->createdByUserHandle = $createdByUserHandle;
    }

    public function setLastModifiedByUserAccount($lastModifiedByUserAccount)
    {
        $this->userAccountEntity->lastModifiedByUserAccount = $lastModifiedByUserAccount;
    }


}