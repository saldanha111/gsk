<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 04/04/2018
 * Time: 13:10
 */

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="documentssignatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\DocumentsSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class DocumentsSignatures
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
     */
    protected $userid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="documentsSignatures")
     * @ORM\JoinColumn(name="userid", referencedColumnName="id")
     */
    protected $userEntiy;

    /**
     * @var integer
     * @ORM\Column(name="groupid", type="integer", nullable=true)
     */
    protected $groupid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="documentsSignatures")
     * @ORM\JoinColumn(name="groupid", referencedColumnName="id")
     */
    protected $groupEntiy;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Documents", inversedBy="signatures")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id")
     */
    protected $document;

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
     */
    protected $attachment;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=90, nullable=true)
     */
    protected $email;


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
     * @return Categories
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
     * @return Categories
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
     * @return DocumentsSignatures
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
     * @return DocumentsSignatures
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
     * @return DocumentsSignatures
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
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return DocumentsSignatures
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
     * @return DocumentsSignatures
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
     * Set document
     *
     * @param \Nononsense\HomeBundle\Entity\Documents $document
     * @return DocumentsSignatures
     */
    public function setDocument(\Nononsense\HomeBundle\Entity\Documents $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \Nononsense\HomeBundle\Entity\Documents 
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set attachment
     *
     * @param boolean $attachment
     * @return DocumentsSignatures
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
     * @return DocumentsSignatures
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
}
