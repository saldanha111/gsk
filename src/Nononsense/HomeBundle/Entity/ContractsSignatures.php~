<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="contracts_signatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ContractsSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ContractsSignatures
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @var integer
     * @ORM\Column(name="userid", type="integer", nullable=true)
     * @Groups({"list_baseS"})
     */
    protected $userid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="contractsSignatures")
     * @ORM\JoinColumn(name="userid", referencedColumnName="id")
     * @Groups({"list_baseS"})
     */
    protected $userEntiy;

    /**
     * @var integer
     * @ORM\Column(name="groupid", type="integer", nullable=true)
     * @Groups({"list_baseS"})
     */
    protected $groupid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="contractsSignatures")
     * @ORM\JoinColumn(name="groupid", referencedColumnName="id")
     * @Groups({"list_baseS"})
     */
    protected $groupEntiy;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Contracts", inversedBy="signatures")
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     */
    protected $contract;

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     */
    protected $number;

    /**
     * @var boolean $attachment
     *
     * @ORM\Column(name="attachment", type="boolean", options={"default" = false})
     * @Groups({"list_baseS"})
     */
    protected $attachment;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=90, nullable=true)
     * @Groups({"list_baseS"})
     */
    protected $email;


    public function __construct()
    {
        $this->created = new \DateTime();
    }

   

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
     * Set created
     *
     * @param \DateTime $created
     * @return ContractsSignatures
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
     * Set modified
     *
     * @param \DateTime $modified
     * @return ContractsSignatures
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime 
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set userid
     *
     * @param integer $userid
     * @return ContractsSignatures
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return integer 
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set groupid
     *
     * @param integer $groupid
     * @return ContractsSignatures
     */
    public function setGroupid($groupid)
    {
        $this->groupid = $groupid;

        return $this;
    }

    /**
     * Get groupid
     *
     * @return integer 
     */
    public function getGroupid()
    {
        return $this->groupid;
    }

    /**
     * Set number
     *
     * @param integer $number
     * @return ContractsSignatures
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer 
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set attachment
     *
     * @param boolean $attachment
     * @return ContractsSignatures
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * Get attachment
     *
     * @return boolean 
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return ContractsSignatures
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
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return ContractsSignatures
     */
    public function setUserEntiy(\Nononsense\UserBundle\Entity\Users $userEntiy = null)
    {
        $this->userEntiy = $userEntiy;

        return $this;
    }

    /**
     * Get userEntiy
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUserEntiy()
    {
        return $this->userEntiy;
    }

    /**
     * Set groupEntiy
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $groupEntiy
     * @return ContractsSignatures
     */
    public function setGroupEntiy(\Nononsense\GroupBundle\Entity\Groups $groupEntiy = null)
    {
        $this->groupEntiy = $groupEntiy;

        return $this;
    }

    /**
     * Get groupEntiy
     *
     * @return \Nononsense\GroupBundle\Entity\Groups 
     */
    public function getGroupEntiy()
    {
        return $this->groupEntiy;
    }

    /**
     * Set contract
     *
     * @param \Nononsense\HomeBundle\Entity\Contracts $contract
     * @return ContractsSignatures
     */
    public function setContract(\Nononsense\HomeBundle\Entity\Contracts $contract = null)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return \Nononsense\HomeBundle\Entity\Contracts 
     */
    public function getContract()
    {
        return $this->contract;
    }
}
