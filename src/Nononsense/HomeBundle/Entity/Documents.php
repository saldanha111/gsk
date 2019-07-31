<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="documents")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\DocumentsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Documents
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="plantilla_id", type="integer")
     */
    protected $plantilla_id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200)
     * @Assert\NotBlank(message = "You shoud insert a name")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=200)
     * @Assert\NotBlank(message = "You shoud insert a description")
     */
    protected $description;

     /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;

    /**
     * @var integer
     *
     * @ORM\Column(name="block", type="integer")
     */
    protected $block;

    /**
     * @var boolean
     *
     * @ORM\Column(name="optional", type="boolean", nullable=true, options={"default" = false})
     */
    protected $optional;

    /**
     * @var integer
     *
     * @ORM\Column(name="dependsOn", type="integer")
     */
    protected $dependsOn;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean",  nullable=true, options={"default" = false})
     */

    protected $isActive;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Types", inversedBy="documents")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsDocuments", mappedBy="document")
     */
    protected $records;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\DocumentsSignatures", mappedBy="document")
     */
    protected $signatures;

    /**
     * @var integer
     * @ORM\Column(name="usercreatedid", type="integer", options={"default" = 3})
     */
    protected $usercreatedid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="documents")
     * @ORM\JoinColumn(name="usercreatedid", referencedColumnName="id")
     */
    protected $userCreatedEntiy;


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
     * Set name
     *
     * @param string $name
     * @return Master_Workflows
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Master_Workflows
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return Master_Workflows
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Master_Workflows
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
     * @return Master_Workflows
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
     * Set plantilla_id
     *
     * @param integer $plantillaId
     * @return Documents
     */
    public function setPlantillaId($plantillaId)
    {
        $this->plantilla_id = $plantillaId;

        return $this;
    }

    /**
     * Get plantilla_id
     *
     * @return integer 
     */
    public function getPlantillaId()
    {
        return $this->plantilla_id;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Documents
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set block
     *
     * @param integer $block
     * @return Documents
     */
    public function setBlock($block)
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Get block
     *
     * @return integer 
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Set optional
     *
     * @param boolean $optional
     * @return Documents
     */
    public function setOptional($optional)
    {
        $this->optional = $optional;

        return $this;
    }

    /**
     * Get optional
     *
     * @return boolean 
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * Set dependsOn
     *
     * @param integer $dependsOn
     * @return Documents
     */
    public function setDependsOn($dependsOn)
    {
        $this->dependsOn = $dependsOn;

        return $this;
    }

    /**
     * Get dependsOn
     *
     * @return integer 
     */
    public function getDependsOn()
    {
        return $this->dependsOn;
    }

    /**
     * Set usercreatedid
     *
     * @param integer $usercreatedid
     * @return Documents
     */
    public function setUsercreatedid($usercreatedid)
    {
        $this->usercreatedid = $usercreatedid;

        return $this;
    }

    /**
     * Get usercreatedid
     *
     * @return integer 
     */
    public function getUsercreatedid()
    {
        return $this->usercreatedid;
    }

    /**
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\Types $type
     * @return Documents
     */
    public function setType(\Nononsense\HomeBundle\Entity\Types $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\Types 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add records
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsDocuments $records
     * @return Documents
     */
    public function addRecord(\Nononsense\HomeBundle\Entity\RecordsDocuments $records)
    {
        $this->records[] = $records;

        return $this;
    }

    /**
     * Remove records
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsDocuments $records
     */
    public function removeRecord(\Nononsense\HomeBundle\Entity\RecordsDocuments $records)
    {
        $this->records->removeElement($records);
    }

    /**
     * Get records
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * Add signatures
     *
     * @param \Nononsense\HomeBundle\Entity\DocumentsSignatures $signatures
     * @return Documents
     */
    public function addSignature(\Nononsense\HomeBundle\Entity\DocumentsSignatures $signatures)
    {
        $this->signatures[] = $signatures;

        return $this;
    }

    /**
     * Remove signatures
     *
     * @param \Nononsense\HomeBundle\Entity\DocumentsSignatures $signatures
     */
    public function removeSignature(\Nononsense\HomeBundle\Entity\DocumentsSignatures $signatures)
    {
        $this->signatures->removeElement($signatures);
    }

    /**
     * Get signatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSignatures()
    {
        return $this->signatures;
    }

    /**
     * Set userCreatedEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userCreatedEntiy
     * @return Documents
     */
    public function setUserCreatedEntiy(\Nononsense\UserBundle\Entity\Users $userCreatedEntiy = null)
    {
        $this->userCreatedEntiy = $userCreatedEntiy;

        return $this;
    }

    /**
     * Get userCreatedEntiy
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUserCreatedEntiy()
    {
        return $this->userCreatedEntiy;
    }
}
