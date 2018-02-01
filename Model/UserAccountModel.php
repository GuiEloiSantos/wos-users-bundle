<?php

namespace MemberPoint\WOS\UsersBundle\Model;

use MemberPoint\WOS\UsersBundle\Entity\UserAccount;
use MemberPoint\WOS\UsersBundle\EntityRepository\UserAccountRepository;
use MemberPoint\WOS\UsersBundle\Exception\ErrorMessageException;
use MemberPoint\WOS\UsersBundle\Exception\InvalidParameterException;
use MemberPoint\WOS\UsersBundle\Utils\Password;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserAccountModel
 * @package MemberPoint\WOS\UsersBundle\Model
 */
class UserAccountModel implements \JsonSerializable
{
    protected $hasUnsavedChanges;
    protected $userAccountEntity;

    protected $isAuthenticated = false;

    /**
     * UserAccountModel constructor.
     * @param array $deps
     */
    public function __construct(array $deps = array())
    {
        $resolver = new OptionsResolver();
        static::configureDependencies($resolver);
        $deps = $resolver->resolve($deps);
        $this->hasUnsavedChanges = false;
        self::setUserAccountRepository($deps['userAccountRepository']);

        if ($deps['userAccountEntity']) {
            $this->userAccountEntity = $deps['userAccountEntity'];
            if (!self::getUserAccountRepository()->containsUser($this->userAccountEntity)) {
                throw new InvalidParameterException('No valid user supplied for the UserModel constructor');
            }
            return;
        } else if ($deps['findById'] != NULL) {
            $this->userAccountEntity = self::getUserAccountRepository()->findOneById($deps['findOneById']);
            $this->userAccountEntity = $this->userAccountEntity!==null?$this->userAccountEntity:new UserAccount();
            if (!self::getUserAccountRepository()->containsUser($this->userAccountEntity)) {
                throw new InvalidParameterException('No valid user id provided');
            }
            return;
        } else if ($deps['findByEmailAddress'] != NULL) {
            $this->userAccountEntity = self::getUserAccountRepository()->findOneByEmailAddress($deps['findByEmailAddress']);
            $this->userAccountEntity = $this->userAccountEntity!==null?$this->userAccountEntity:new UserAccount();
            if (!self::getUserAccountRepository()->containsUser($this->userAccountEntity)) {
                throw new ErrorMessageException('Invalid credentials');
            }
            return;
        }

    }

    /**
     * @param OptionsResolver $resolver
     */
    private static function configureDependencies(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'userAccountRepository' => null,
                'userAccountEntity' => null,
                'findById' => null,
                'findByEmailAddress' => null
            )
        );

        $resolver->setAllowedTypes('userAccountEntity', array('MemberPoint\WOS\UsersBundle\Entity\UserAccount', 'null'));
        $resolver->setAllowedTypes('userAccountRepository', array('MemberPoint\WOS\UsersBundle\EntityRepository\UserAccountRepository', 'null'));
        $resolver->setAllowedTypes('findById', array('integer', 'null'));
        $resolver->setAllowedTypes('findByEmailAddress', array('string', 'null'));

    }

    /**
     * @param $userAccountRepository
     * @return mixed
     */
    private static function setUserAccountRepository($userAccountRepository)
    {
        return UserAccountRepository::setUserAccountRepository($userAccountRepository);
    }

    /**
     * @return mixed
     */
    private static function getUserAccountRepository()
    {
        return UserAccountRepository::getUserAccountRepository();
    }

    /**
     * @param array $deps
     * @return boolean|UserAccountModel
     */
    public static function createNewUser(array $deps = array())
    {

        $resolver = new OptionsResolver();
        static::configureNewUserDependencies($resolver);
        $deps = $resolver->resolve($deps);

        $userAccountModel = new UserAccountModel(
            array(
                'userAccountRepository' => self::getUserAccountRepository(),
                'userAccountEntity' => new UserAccount()
            )
        );
        $actionUserAccount = $deps['actionUserAccount'];
        $userAccountModel->setFirstName($deps['firstName'])
            ->setLastName($deps['lastName'])
            ->setEmailAddress($deps['emailAddress'])
            ->setPassword($deps['password'])
            ->setNationalId($deps['nationalId'])
            ->setMobilePhoneNumber($deps['mobilePhoneNumber'])
            ->setCreatedByUserAccount($actionUserAccount ? $actionUserAccount->getId() : 0)
            ->setLastModifiedByUserAccount($actionUserAccount ? $actionUserAccount->getId() : 0);

        self::getUserAccountRepository()->newUser($userAccountModel->userAccountEntity);

        $userAccountModel->isAuthenticated = true;
        return self::getUserAccountRepository()->containsUser($userAccountModel->userAccountEntity) ? $userAccountModel : false;
    }

    /**
     * @param OptionsResolver $resolver
     */
    private static function configureNewUserDependencies(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'firstName' => null,
                'lastName' => null,
                'emailAddress' => null,
                'password' => null,
                'nationalId' => '',
                'mobilePhoneNumber' => null,
                'actionUserAccount' => null
            )
        );

        $resolver->setAllowedTypes('firstName', array('string'));
        /*$resolver->setAllowedValues('firstName', function ($value) {
            $length = strlen($value);
            return array_search($length, range(1, 79)) ? true : false;
        });*/

        $resolver->setAllowedTypes('lastName', array('string'));
        /*$resolver->setAllowedValues('lastName', function ($value) {
            $length = strlen($value);
            return array_search($length, range(1, 319)) ? true : false;
        });*/

        $resolver->setAllowedTypes('emailAddress', array('string'));
        /*$resolver->setAllowedValues('emailAddress', function ($value) {
            $length = strlen($value);
            return array_search($length, range(1, 319)) ? true : false;
        });*/

        $resolver->setAllowedTypes('password', array('string'));
        /*$resolver->setAllowedValues('password', function ($value) {
            $length = strlen($value);
            return array_search($length, range(1, 31)) ? true : false;
        });*/

        $resolver->setAllowedTypes('nationalId', array('string', 'null'));
        $resolver->setAllowedTypes('mobilePhoneNumber', array('string', 'null'));
        $resolver->setAllowedTypes('actionUserAccount', array('MemberPoint\WOS\UsersBundle\Model\UserAccountModel', 'null'));

        $resolver->setRequired('firstName');
        $resolver->setRequired('lastName');
        $resolver->setRequired('emailAddress');
        $resolver->setRequired('password');
    }

    /**
     * @param $lastModifiedByUserAccount
     * @return UserAccountModel
     */
    public function setLastModifiedByUserAccount($lastModifiedByUserAccount)
    {
        $this->userAccountEntity->lastModifiedByUserAccount = $lastModifiedByUserAccount;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param $createdByUserAccount
     * @return UserAccountModel
     */
    public function setCreatedByUserAccount($createdByUserAccount)
    {
        $this->userAccountEntity->createdByUserAccount = $createdByUserAccount;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param $mobilePhoneNumber
     * @return UserAccountModel
     */
    public function setMobilePhoneNumber($mobilePhoneNumber)
    {
        $this->userAccountEntity->mobilePhoneNumber = $mobilePhoneNumber;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param $nationalId
     * @return UserAccountModel
     */
    public function setNationalId($nationalId)
    {
        $this->userAccountEntity->nationalId = $nationalId;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param $password
     * @return bool|UserAccountModel
     */
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
            return $this;
        } else {
            throw new ErrorMessageException(implode(' ', $passwordValidator->getErrors()));
        }
    }

    /**
     * @param $emailAddress
     * @return  UserAccountModel
     */
    private function setEmailAddress($emailAddress)
    {
        if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $this->userAccountEntity->emailAddress = $emailAddress;
            $this->hasUnsavedChanges = true;
            return $this;
        }
        throw new ErrorMessageException('Invalid Email');
    }

    /**
     * @param $lastName
     * @return UserAccountModel
     */
    public function setLastName($lastName)
    {
        $this->userAccountEntity->lastName = $lastName;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param $firstName
     * @return UserAccountModel
     */
    public function setFirstName($firstName)
    {
        $this->userAccountEntity->firstName = $firstName;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param \DateTime $createdDttm
     * @return UserAccountModel
     */
    public function setCreatedDttm(\DateTime $createdDttm)
    {
        $this->userAccountEntity->createdDttm = $createdDttm;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param $lastModifiedByUserHandle
     * @return UserAccountModel
     */
    public function setLastModifiedByUserHandle($lastModifiedByUserHandle)
    {
        $this->userAccountEntity->lastModifiedByUserHandle = $lastModifiedByUserHandle;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param \DateTime $tokenExpireDttm
     * @return UserAccountModel
     */
    public function setTokenExpireDttm(\DateTime $tokenExpireDttm)
    {
        $this->userAccountEntity->tokenExpireDttm = $tokenExpireDttm;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param \DateTime $lastLoginAttemptDttm
     * @return UserAccountModel
     */
    public function setLastLoginAttemptDttm(\DateTime $lastLoginAttemptDttm)
    {
        $this->userAccountEntity->lastLoginAttemptDttm = $lastLoginAttemptDttm;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param $countLoginAttemptFailed
     * @return UserAccountModel
     */
    public function setCountLoginAttemptFailed($countLoginAttemptFailed)
    {
        $this->userAccountEntity->countLoginAttemptFailed = $countLoginAttemptFailed;
        $this->hasUnsavedChanges = true;
        return $this;
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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->userAccountEntity->id;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->userAccountEntity->firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->userAccountEntity->lastName;
    }

    /**
     * @return mixed
     */
    public function getEmailAddress()
    {
        return $this->userAccountEntity->emailAddress;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->userAccountEntity->password;
    }

    /**
     * @return mixed
     */
    public function getNationalId()
    {
        return $this->userAccountEntity->nationalId;
    }

    /**
     * @return mixed
     */
    public function getMobilePhoneNumber()
    {
        return $this->userAccountEntity->mobilePhoneNumber;
    }

    /**
     * @return mixed
     */
    public function getCreatedByUserHandle()
    {
        return $this->userAccountEntity->createdByUserHandle;
    }

    /**
     * @return mixed
     */
    public function getCreatedByUserAccount()
    {
        return $this->userAccountEntity->createdByUserAccount;
    }

    /**
     * @return mixed
     */
    public function getCreatedDttm()
    {
        return $this->userAccountEntity->createdDttm;
    }

    /**
     * @return mixed
     */
    public function getLastModifiedByUserHandle()
    {
        return $this->userAccountEntity->lastModifiedByUserHandle;
    }

    /**
     * @return mixed
     */
    public function getLastModifiedByUserAccount()
    {
        return $this->userAccountEntity->lastModifiedByUserAccount;
    }

    /**
     * @return mixed
     */
    public function getLastModifiedDttm()
    {
        return $this->userAccountEntity->lastModifiedDttm;
    }

    /**
     * @return mixed
     */
    public function getAccountVerified()
    {
        return $this->userAccountEntity->accountVerified;
    }

    /**
     * @return mixed
     */
    public function getTokenExpireDttm()
    {
        return $this->userAccountEntity->tokenExpireDttm;
    }

    /**
     * @return mixed
     */
    public function getLastLoginAttemptDttm()
    {
        return $this->userAccountEntity->lastLoginAttemptDttm;
    }

    /**
     * @return mixed
     */
    public function getCountLoginAttemptFailed()
    {
        return $this->userAccountEntity->countLoginAttemptFailed;
    }

    /**
     * @param UserAccountModel $actionUserAccount
     * @return UserAccountModel
     */
    public function update(UserAccountModel $actionUserAccount)
    {
        if ($actionUserAccount->isAuthenticated) {
            //@TODO has permission or is itself
            $this->setLastModifiedByUserAccount($actionUserAccount->getId());
            $this->setLastModifiedByUserAccount($actionUserAccount->getEmailAddress());
            $this->setLastModifiedDttm(new \DateTime('now'));
            self::getUserAccountRepository()->updateUser($this->userAccountEntity);
        }
        return $this;
    }

    /**
     * @param \DateTime $lastModifiedDttm
     * @return UserAccountModel
     */
    public function setLastModifiedDttm(\DateTime $lastModifiedDttm)
    {
        $this->userAccountEntity->lastModifiedDttm = $lastModifiedDttm;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param $createdByUserHandle
     * @return UserAccountModel
     */
    public function setCreatedByUserHandle($createdByUserHandle)
    {
        $this->userAccountEntity->createdByUserHandle = $createdByUserHandle;
        $this->hasUnsavedChanges = true;
        return $this;
    }

    /**
     * @param $password
     * @return UserAccountModel
     */
    public function authenticate($password)
    {
        $hash = $this->getPassword();
        $this->isAuthenticated = password_verify($password, $hash );

        if (!$this->isAuthenticated)
            throw new ErrorMessageException('Invalid credentials');
        return $this;
    }

    /**
     * @return void
     */
    public function sendForgetPasswordEmail()
    {
        //@TODO Send email

    }

    /**
     * @return bool
     */
    public function isAuthenticate()
    {
        return $this->isAuthenticated;
    }

}