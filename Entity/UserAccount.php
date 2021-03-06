<?php

namespace  MemberPoint\WOS\UsersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="MemberPoint\WOS\UsersBundle\EntityRepository\UserAccountRepository")
 * @ORM\Table(name="wos_users__user_account")
 * @ORM\HasLifecycleCallbacks
 */
class UserAccount
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string", name="first_name", length=80, nullable=false)
     * @Assert\Length(
     *      min = 2,
     *      max = 80,
     *      minMessage = "Your last name must be at least {{ limit }} characters long",
     *      maxMessage = "Your last name cannot be longer than {{ limit }} characters"
     * )
     */
    public $firstName;

    /**
     * @ORM\Column(type="string", name="last_name", length=80, nullable=false)
     * @Assert\Length(
     *      min = 2,
     *      max = 80,
     *      minMessage = "Your last name must be at least {{ limit }} characters long",
     *      maxMessage = "Your last name cannot be longer than {{ limit }} characters"
     * )
     */
    public $lastName;

    /**
     * @ORM\Column(type="string", name="email_address", length=320, nullable=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     */
    public $emailAddress;

    /**
     * @ORM\Column(type="string", name="password", length=320, nullable=false, unique=true)
     * @Assert\NotBlank()
     */
    public $password;

    /**
     * @ORM\Column(type="string", name="nin", length=32, nullable=true)
     */
    public $nationalId;

    /**
     * @ORM\Column(type="string", name="mobile_phone_number", length=24, nullable=true)
     */
    public $mobilePhoneNumber;

    /**
     * @ORM\ManyToOne(targetEntity="MemberPoint\WOS\UsersBundle\Entity\UserAccount")
     * @ORM\Column(type="string", name="created_by_user_account")
     * @ORM\JoinColumn(name="created_by_user_account", referencedColumnName="id")
     */
    public $createdByUserAccount;

    /**
     * @ORM\Column(type="string", name="created_by_user_handle", length=64)
     */
    public $createdByUserHandle = "";

    /**
     * @ORM\Column(type="date", name="created_dttm")
     */
    public $createdDttm;

    /**
     * @ORM\ManyToOne(targetEntity="MemberPoint\WOS\UsersBundle\Entity\UserAccountEntity")
     * @ORM\Column(type="string", name="last_modified_by_user_account")
     * @ORM\JoinColumn(name="last_modified_by_user_account", referencedColumnName="id")
     */
    public $lastModifiedByUserAccount;

    /**
     * @ORM\Column(type="string", name="last_modified_by_user_handle", length=64)
     */
    public $lastModifiedByUserHandle ="";

    /**
     * @ORM\Column(type="date", name="last_modified_dttm")
     */
    public $lastModifiedDttm;

    /**
     * @ORM\Column(type="boolean", name="is_verified")
     */
    public $accountVerified = false;

    /**
     * @ORM\Column(type="date", name="token_expire_dttm")
     */
    public $tokenExpireDttm;

    /**
     * @ORM\Column(type="date", name="last_login_attempt")
     */
    public $lastLoginAttemptDttm;

    /**
     * @ORM\Column(type="integer", name="count_login_attempt_failed", nullable=false)
     * @Assert\NotBlank()
     */
    public $countLoginAttemptFailed = 0;

    /**
     *  @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdDttm = $this->createdDttm?$this->createdDttm: new \DateTime("now");
        $this->lastModifiedDttm =  new \DateTime("now");
        $this->tokenExpireDttm =  new \DateTime();
        $this->lastLoginAttemptDttm =  new \DateTime();
    }
}