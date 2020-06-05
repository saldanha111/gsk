<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="Records_contracts_signatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RecordsContractsSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RecordsContractsSignatures
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @var integer
     * @ORM\Column(name="userid", type="integer", nullable=true)
     */
    protected $userid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="recordsContractsSignatures")
     * @ORM\JoinColumn(name="userid", referencedColumnName="id")
     */
    protected $userEntiy;

    /**
     * @var integer
     * @ORM\Column(name="groupid", type="integer", nullable=true)
     */
    protected $groupid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="recordsContractsSignatures")
     * @ORM\JoinColumn(name="groupid", referencedColumnName="id")
     */
    protected $groupEntiy;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContracts", inversedBy="signatures")
     * @ORM\JoinColumn(name="record_id", referencedColumnName="id")
     */
    protected $record;


    /**
     * @var string
     *
     * @ORM\Column(name="firma", type="text", nullable=true)
     */
    protected $firma;

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     */
    protected $number;

    /**
     * @var boolean $next
     *
     * @ORM\Column(name="next", type="boolean", options={"default" = false}, nullable=true)
     */
    protected $next;

    /**
     * @var boolean $attachment
     *
     * @ORM\Column(name="attachment", type="boolean", options={"default" = false}, nullable=true)
     */
    protected $attachment;

    /**
     * @var string
     *
     * @ORM\Column(name="files", type="text", nullable=true)
     *
     */
    protected $file;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=90, nullable=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text",  nullable=true)
     *
     */
    protected $comments;



    public function __construct()
    {

    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        if (!$this->created) {
            $this->created = new \DateTime();
        }
        $this->modified = $this->created;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setModifiedValue()
    {
        $this->modified = new \DateTime();
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
     * @return RecordsContractsSignatures
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
     * @return RecordsContractsSignatures
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
     * @return RecordsContractsSignatures
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
     * @return RecordsContractsSignatures
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
     * Set firma
     *
     * @param string $firma
     * @return RecordsContractsSignatures
     */
    public function setFirma($firma)
    {
        $this->firma = $firma;

        return $this;
    }

    /**
     * Get firma
     *
     * @return string 
     */
    public function getFirma()
    {
        return $this->firma;
    }

    /**
     * Set number
     *
     * @param integer $number
     * @return RecordsContractsSignatures
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
     * Set next
     *
     * @param boolean $next
     * @return RecordsContractsSignatures
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next
     *
     * @return boolean 
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set attachment
     *
     * @param boolean $attachment
     * @return RecordsContractsSignatures
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
     * Set file
     *
     * @param string $file
     * @return RecordsContractsSignatures
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return RecordsContractsSignatures
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
     * Set comments
     *
     * @param string $comments
     * @return RecordsContractsSignatures
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return RecordsContractsSignatures
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
     * @return RecordsContractsSignatures
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
     * Set record
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContracts $record
     * @return RecordsContractsSignatures
     */
    public function setRecord(\Nononsense\HomeBundle\Entity\RecordsContracts $record = null)
    {
        $this->record = $record;

        return $this;
    }

    /**
     * Get record
     *
     * @return \Nononsense\HomeBundle\Entity\RecordsContracts 
     */
    public function getRecord()
    {
        return $this->record;
    }
}
