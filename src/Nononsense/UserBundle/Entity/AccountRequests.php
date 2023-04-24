<?php

namespace Nononsense\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AccountRequests
 *
 * @ORM\Table(name="account_requests")
 * @ORM\Entity(repositoryClass="Nononsense\UserBundle\Entity\AccountRequestsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class AccountRequests
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="mud_id", type="string", length=20)
     */
    private $mudId;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=90)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_username", type="string", length=90, nullable=true)
     */
    private $refUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=90)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="date")
     */
    private $created;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activeDirectory", type="boolean", options={"default": true})
     */
    private $activeDirectory = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     * @Assert\Regex(
     *     pattern     = "/^([0-1])$/",
     *     message     = "Error al procesar el estado."
     * )
     */
    private $status;

    /**
    * @ORM\OneToMany(targetEntity="AccountRequestsGroups", mappedBy="requestId", cascade={"persist", "remove"})
    */
    private $request;

    /**
     * @var boolean
     *
     * @ORM\Column(name="requestType", type="boolean", nullable=true)
     */
    private $requestType = 0;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mudId
     *
     * @param string $mudId
     * @return AccountRequests
     */
    public function setMudId($mudId)
    {
        $this->mudId = $mudId;

        return $this;
    }

    /**
     * Get mudId
     *
     * @return string 
     */
    public function getMudId()
    {
        return $this->mudId;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return AccountRequests
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return AccountRequests
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return AccountRequests
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return AccountRequests
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set activeDirectory
     *
     * @param boolean $activeDirectory
     * @return AccountRequests
     */
    public function setActiveDirectory($activeDirectory)
    {
        $this->activeDirectory = $activeDirectory;

        return $this;
    }

    /**
     * Get activeDirectory
     *
     * @return boolean 
     */
    public function getActiveDirectory()
    {
        return $this->activeDirectory;
    }

    /**
     * Set status
     *
     * @param boolean $activeDirectory
     * @return AccountRequests
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        if (!$this->created) {
            $this->created = new \DateTime();
        }
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->request = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add request
     *
     * @param \Nononsense\UserBundle\Entity\AccountRequestsGroups $request
     * @return AccountRequests
     */
    public function addRequest(\Nononsense\UserBundle\Entity\AccountRequestsGroups $request)
    {
        $this->request[] = $request;

        return $this;
    }

    /**
     * Remove request
     *
     * @param \Nononsense\UserBundle\Entity\AccountRequestsGroups $request
     */
    public function removeRequest(\Nononsense\UserBundle\Entity\AccountRequestsGroups $request)
    {
        $this->request->removeElement($request);
    }

    /**
     * Get request
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set requestType
     *
     * @param boolean $requestType
     * @return AccountRequests
     */
    public function setRequestType($requestType)
    {
        $this->requestType = $requestType;

        return $this;
    }

    /**
     * Get requestType
     *
     * @return boolean 
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * Set refUsername
     *
     * @param string $refUsername
     * @return AccountRequests
     */
    public function setRefUsername($refUsername)
    {
        $this->refUsername = $refUsername;

        return $this;
    }

    /**
     * Get refUsername
     *
     * @return string 
     */
    public function getRefUsername()
    {
        return $this->refUsername;
    }
}
