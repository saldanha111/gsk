<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 03/04/2018
 * Time: 13:45
 */

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="recordsdocuments")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RecordsDocumentsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RecordsDocuments
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
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Types", inversedBy="records")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
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
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Documents", inversedBy="records")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id")
     */
    protected $document;

    /**
     * @var integer
     * @ORM\Column(name="usercreatedid", type="integer", options={"default" = 3})
     */
    protected $usercreatedid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="recordsDocuments")
     * @ORM\JoinColumn(name="usercreatedid", referencedColumnName="id")
     */
    protected $userCreatedEntiy;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RecordsSignatures", mappedBy="record")
     */
    protected $signatures;


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
     * @return InstanciasWorkflows
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
     * Set masterDataValues
     *
     * @param string $masterDataValues
     * @return InstanciasWorkflows
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
     * Set files
     *
     * @param string $files
     * @return InstanciasWorkflows
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
     * @return InstanciasWorkflows
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
     * @return InstanciasWorkflows
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
     * @return InstanciasWorkflows
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
     * Set in_edition
     *
     * @param boolean $inEdition
     * @return InstanciasWorkflows
     */
    public function setInEdition($inEdition)
    {
        $this->in_edition = $inEdition;

        return $this;
    }

    /**
     * Get in_edition
     *
     * @return boolean 
     */
    public function getInEdition()
    {
        return $this->in_edition;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return InstanciasWorkflows
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
     * Set status
     *
     * @param integer $status
     * @return InstanciasWorkflows
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
     * @return InstanciasWorkflows
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
     * @return InstanciasWorkflows
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
     * Add metaData
     *
     * @param \Nononsense\HomeBundle\Entity\MetaData $metaData
     * @return InstanciasWorkflows
     */
    public function addMetaDatum(\Nononsense\HomeBundle\Entity\MetaData $metaData)
    {
        $this->metaData[] = $metaData;

        return $this;
    }

    /**
     * Remove metaData
     *
     * @param \Nononsense\HomeBundle\Entity\MetaData $metaData
     */
    public function removeMetaDatum(\Nononsense\HomeBundle\Entity\MetaData $metaData)
    {
        $this->metaData->removeElement($metaData);
    }

    /**
     * Get metaData
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMetaData()
    {
        return $this->metaData;
    }


    /**
     * Set userCreatedEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userCreatedEntiy
     * @return InstanciasWorkflows
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
     * Set dependsOn
     *
     * @param integer $dependsOn
     * @return RecordsDocuments
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
     * Set stepDataValue
     *
     * @param string $stepDataValue
     * @return RecordsDocuments
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
     * @return RecordsDocuments
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
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\Types $type
     * @return RecordsDocuments
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
     * Set document
     *
     * @param \Nononsense\HomeBundle\Entity\Documents $document
     * @return RecordsDocuments
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
     * Add signatures
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsSignatures $signatures
     * @return RecordsDocuments
     */
    public function addSignature(\Nononsense\HomeBundle\Entity\RecordsSignatures $signatures)
    {
        $this->signatures[] = $signatures;

        return $this;
    }

    /**
     * Remove signatures
     *
     * @param \Nononsense\HomeBundle\Entity\RecordsSignatures $signatures
     */
    public function removeSignature(\Nononsense\HomeBundle\Entity\RecordsSignatures $signatures)
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
     * Set lastSign
     *
     * @param integer $lastSign
     * @return RecordsDocuments
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
     * Set comments
     *
     * @param string $comments
     * @return RecordsDocuments
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
}
