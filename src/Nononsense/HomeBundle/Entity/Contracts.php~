<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="contracts")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ContractsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Contracts
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_contract"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="plantilla_id", type="string", length=200)
     * @Groups({"detail_contract"})
     */
    protected $plantilla_id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200)
     * @Assert\NotBlank(message = "You shoud insert a name")
     * @Groups({"detail_contract"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=200)
     * @Assert\NotBlank(message = "You shoud insert a description")
     * @Groups({"detail_contract"})
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
     * @Groups({"detail_contract"})
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
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ContractsTypes", inversedBy="Contracts")
     * @ORM\JoinColumn(name="contract_type_id", referencedColumnName="id")
     * @Groups({"detail_contract"})
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContracts", mappedBy="contract")
     */
    protected $records;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ContractsSignatures", mappedBy="contract")
     */
    protected $signatures;

    /**
     * @var integer
     * @ORM\Column(name="usercreatedid", type="integer", options={"default" = 3})
     */
    protected $usercreatedid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="contracts")
     * @ORM\JoinColumn(name="usercreatedid", referencedColumnName="id")
     */
    protected $userCreatedEntiy;

    /**
     * @var boolean $signCreator
     *
     * @ORM\Column(name="sign_creator", type="boolean",  nullable=true, options={"default" = false})
     * @Groups({"detail_contract"})
     */
    protected $signCreator;

    /**
     * @var boolean $attachment
     *
     * @ORM\Column(name="attachment", type="boolean", options={"default" = false}, nullable=true)
     * @Groups({"detail_contract"})
     */
    protected $attachment;



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
     * Set plantilla_id
     *
     * @param integer $plantillaId
     * @return Contracts
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
     * Set name
     *
     * @param string $name
     * @return Contracts
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
     * @return Contracts
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
     * Set position
     *
     * @param integer $position
     * @return Contracts
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
     * @return Contracts
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
     * @return Contracts
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
     * @return Contracts
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return Contracts
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
     * @return Contracts
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
     * @return Contracts
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
     * Set usercreatedid
     *
     * @param integer $usercreatedid
     * @return Contracts
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
     * Set signCreator
     *
     * @param boolean $signCreator
     * @return Contracts
     */
    public function setSignCreator($signCreator)
    {
        $this->signCreator = $signCreator;

        return $this;
    }

    /**
     * Get signCreator
     *
     * @return boolean 
     */
    public function getSignCreator()
    {
        return $this->signCreator;
    }

    /**
     * Set attachment
     *
     * @param boolean $attachment
     * @return Contracts
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
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\ContractsTypes $type
     * @return Contracts
     */
    public function setType(\Nononsense\HomeBundle\Entity\ContractsTypes $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\ContractsTypes 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add records
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContracts $records
     * @return Contracts
     */
    public function addRecord(\Nononsense\HomeBundle\Entity\RecordsContracts $records)
    {
        $this->records[] = $records;

        return $this;
    }

    /**
     * Remove records
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContracts $records
     */
    public function removeRecord(\Nononsense\HomeBundle\Entity\RecordsContracts $records)
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
     * @param \Nononsense\HomeBundle\Entity\ContractsSignatures $signatures
     * @return Contracts
     */
    public function addSignature(\Nononsense\HomeBundle\Entity\ContractsSignatures $signatures)
    {
        $this->signatures[] = $signatures;

        return $this;
    }

    /**
     * Remove signatures
     *
     * @param \Nononsense\HomeBundle\Entity\ContractsSignatures $signatures
     */
    public function removeSignature(\Nononsense\HomeBundle\Entity\ContractsSignatures $signatures)
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
     * @return Contracts
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
