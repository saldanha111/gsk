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
     * @var array
     *
     * @ORM\Column(name="groups", type="array")
     */
    private $groups;

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
     * @ORM\Column(name="activeDirectory", type="boolean", options={"default":0})
     */
    private $activeDirectory;

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
     * Set groups
     *
     * @param array $groups
     * @return AccountRequests
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get groups
     *
     * @return array 
     */
    public function getGroups()
    {
        return $this->groups;
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
}
