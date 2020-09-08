<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="records_contracts")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RecordsContractsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RecordsContracts
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=90)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ContractsTypes", inversedBy="records")
     * @ORM\JoinColumn(name="contract_type_id", referencedColumnName="id")
     */
    protected $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="dependsOn", type="integer")
     */
    protected $dependsOn;

    /**
     * @var string
     *
     * @ORM\Column(name="masterdatavalues", type="text")
     *
     */
    protected $masterDataValues;

    /**
     * @var string
     *
     * @ORM\Column(name="stepdatavalue", type="text")
     *
     */
    protected $stepDataValue;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=90)
     */
    protected $token;

    /**
     * @var string
     *
     * @ORM\Column(name="files", type="text")
     *
     */
    protected $files;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean",  nullable=true, options={"default" = false})
     */
    protected $isActive;

    /**
     * @var integer
     *
     * @ORM\Column(name="last_sign", type="integer",  nullable=true)
     */
    protected $lastSign;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="in_edition", type="integer",  nullable=true, options={"default" = 0})
     */

    protected $in_edition;

    /**
     * @var string
     *
     * @ORM\Column(name="observaciones", type="text")
     *
     */
    protected $observaciones;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text",  nullable=true)
     *
     */
    protected $comments;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", options={"default" = 0})
     *
     */
    protected $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer", options={"default" = 2019})
     *
     */
    protected $year;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Contracts", inversedBy="records")
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     */
    protected $contract;

    /**
     * @var integer
     * @ORM\Column(name="usercreatedid", type="integer", options={"default" = 3})
     */
    protected $usercreatedid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="recordsContracts")
     * @ORM\JoinColumn(name="usercreatedid", referencedColumnName="id")
     */
    protected $userCreatedEntiy;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContractsSignatures", mappedBy="record")
     */
    protected $signatures;

    /**
     * @var string
     *
     * @ORM\Column(name="pin", type="string", length=10, nullable=true)
     */
    protected $pin;

    /**
     * @var string
     *
     * @ORM\Column(name="token_public_signature", type="string", length=50, nullable=true)
     */
    protected $tokenPublicSignature;

    /**
     * @var string
     *
     * @ORM\Column(name="token_public_signature_comite", type="string", length=50, nullable=true)
     */
    protected $tokenPublicSignatureComite;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsContractsPinComite", mappedBy="contract")
     */
    protected $pinComite;


    /**
     * InstanciasWorkflows constructor.
     */

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
     * Set description
     *
     * @param string $description
     * @return RecordsContracts
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
     * Set dependsOn
     *
     * @param integer $dependsOn
     * @return RecordsContracts
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
     * Set masterDataValues
     *
     * @param string $masterDataValues
     * @return RecordsContracts
     */
    public function setMasterDataValues($masterDataValues)
    {
        $this->masterDataValues = $masterDataValues;

        return $this;
    }

    /**
     * Get masterDataValues
     *
     * @return string 
     */
    public function getMasterDataValues()
    {
        return $this->masterDataValues;
    }

    /**
     * Set stepDataValue
     *
     * @param string $stepDataValue
     * @return RecordsContracts
     */
    public function setStepDataValue($stepDataValue)
    {
        $this->stepDataValue = $stepDataValue;

        return $this;
    }

    /**
     * Get stepDataValue
     *
     * @return string 
     */
    public function getStepDataValue()
    {
        return $this->stepDataValue;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return RecordsContracts
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set files
     *
     * @param string $files
     * @return RecordsContracts
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Get files
     *
     * @return string 
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return RecordsContracts
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
     * @return RecordsContracts
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return RecordsContracts
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
     * Set lastSign
     *
     * @param integer $lastSign
     * @return RecordsContracts
     */
    public function setLastSign($lastSign)
    {
        $this->lastSign = $lastSign;

        return $this;
    }

    /**
     * Get lastSign
     *
     * @return integer 
     */
    public function getLastSign()
    {
        return $this->lastSign;
    }

    /**
     * Set in_edition
     *
     * @param integer $inEdition
     * @return RecordsContracts
     */
    public function setInEdition($inEdition)
    {
        $this->in_edition = $inEdition;

        return $this;
    }

    /**
     * Get in_edition
     *
     * @return integer 
     */
    public function getInEdition()
    {
        return $this->in_edition;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return RecordsContracts
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set comments
     *
     * @param string $comments
     * @return RecordsContracts
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
     * Set status
     *
     * @param integer $status
     * @return RecordsContracts
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return RecordsContracts
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set usercreatedid
     *
     * @param integer $usercreatedid
     * @return RecordsContracts
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
     * @param \Nononsense\HomeBundle\Entity\ContractsTypes $type
     * @return RecordsContracts
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
     * Set contract
     *
     * @param \Nononsense\HomeBundle\Entity\Contracts $contract
     * @return RecordsContracts
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

    /**
     * Set userCreatedEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userCreatedEntiy
     * @return RecordsContracts
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

    /**
     * Add signatures
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContractsSignatures $signatures
     * @return RecordsContracts
     */
    public function addSignature(\Nononsense\HomeBundle\Entity\RecordsContractsSignatures $signatures)
    {
        $this->signatures[] = $signatures;

        return $this;
    }

    /**
     * Remove signatures
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContractsSignatures $signatures
     */
    public function removeSignature(\Nononsense\HomeBundle\Entity\RecordsContractsSignatures $signatures)
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
     * Set pin
     *
     * @param string $pin
     * @return RecordsContracts
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Get pin
     *
     * @return string 
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Set tokenPublicSignature
     *
     * @param string $tokenPublicSignature
     * @return RecordsContracts
     */
    public function setTokenPublicSignature($tokenPublicSignature)
    {
        $this->tokenPublicSignature = $tokenPublicSignature;

        return $this;
    }

    /**
     * Get tokenPublicSignature
     *
     * @return string 
     */
    public function getTokenPublicSignature()
    {
        return $this->tokenPublicSignature;
    }

    /**
     * Set tokenPublicSignatureComite
     *
     * @param string $tokenPublicSignatureComite
     * @return RecordsContracts
     */
    public function setTokenPublicSignatureComite($tokenPublicSignatureComite)
    {
        $this->tokenPublicSignatureComite = $tokenPublicSignatureComite;

        return $this;
    }

    /**
     * Get tokenPublicSignatureComite
     *
     * @return string 
     */
    public function getTokenPublicSignatureComite()
    {
        return $this->tokenPublicSignatureComite;
    }

    /**
     * Add pinComite
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContractsPinComite $pinComite
     * @return RecordsContracts
     */
    public function addPinComite(\Nononsense\HomeBundle\Entity\RecordsContractsPinComite $pinComite)
    {
        $this->pinComite[] = $pinComite;

        return $this;
    }

    /**
     * Remove pinComite
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsContractsPinComite $pinComite
     */
    public function removePinComite(\Nononsense\HomeBundle\Entity\RecordsContractsPinComite $pinComite)
    {
        $this->pinComite->removeElement($pinComite);
    }

    /**
     * Get pinComite
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPinComite()
    {
        return $this->pinComite;
    }
}
