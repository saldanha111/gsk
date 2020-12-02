<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_templates")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMTemplatesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMTemplates
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_document","json","detail","detail_area"})
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200)
     * @Assert\NotBlank(message = "You shoud insert a name")
     * @Groups({"detail_document","json","detail","detail_area"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="prefix", type="string")
     * @Groups({"detail_document","json","detail_area"})
     */
    protected $prefix;

    
    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string")
     * @Groups({"detail_document","json","detail","detail_area"})
     */
    protected $number;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", nullable=true)
     * @Groups({"detail_document","json"})
     */
    protected $reference;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_id", type="integer", nullable=true)
     */
    protected $number_id;


    /**
     * @var integer
     *
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    protected $template_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="num_edition", type="integer", nullable=true)
     */
    protected $numEdition;

    /**
     * @var integer
     *
     * @ORM\Column(name="first_edtition", type="integer", nullable=true)
     */
    protected $firstEdition;

    /**
     * @var string
     *
     * @ORM\Column(name="plantilla_id", type="string")
     */
    protected $plantilla_id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank(message = "You shoud insert a description")
     * @Groups({"detail_document"})
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="history_change", type="text")
     * @Assert\NotBlank(message = "You shoud insert a request")
     * @Groups({"detail_document"})
     */
    protected $historyChange;

    /**
     * @var boolean $logbook
     *
     * @ORM\Column(name="logbook", type="boolean",  options={"default" = false})
     * @Groups({"detail_document"})
     */
    protected $logbook;

    /**
     * @var boolean $uniqid
     *
     * @ORM\Column(name="uniqid", type="boolean",  options={"default" = false})
     * @Groups({"detail_document"})
     */
    protected $uniqid;

    /**
     * @var boolean $isSimple
     *
     * @ORM\Column(name="is_simple", type="boolean",  options={"default" = false})
     * @Groups({"detail_document"})
     */
    protected $isSimple;

    /**
     * @var boolean $inactive
     *
     * @ORM\Column(name="inactive", type="boolean",  options={"default" = false}, nullable=true)
     * @Groups({"detail_document"})
     */
    protected $inactive;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $estimatedEffectiveDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $effectiveDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $reviewDate;

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\RetentionCategories", inversedBy="templates")
     * @ORM\JoinTable(name="tm_retentions")
     */
    private $retentions;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Areas", inversedBy="tmTemplates")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id")
     * @Groups({"json"})
     */
    protected $area;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\QrsTypes", inversedBy="tmTemplates")
     * @ORM\JoinColumn(name="qr_type_id", referencedColumnName="id", nullable=true)
     */
    protected $QRType;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMStates", inversedBy="tmTemplates")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     */
    protected $tmState;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMSignatures", mappedBy="template")
     */
    protected $tmSignatures;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMWorkflow", mappedBy="template")
     */
    protected $tmWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMSecondWorkflow", mappedBy="template")
     */
    protected $tmSecondWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMNestTemplates", mappedBy="template")
     */
    protected $tmNestMasterTemplates;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMNestTemplates", mappedBy="nestTemplate")
     */
    protected $tmNestTemplates;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Areas", mappedBy="template")
     */
    protected $areas;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="ownerTemplates")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="backupTeamplates")
     * @ORM\JoinColumn(name="backup_id", referencedColumnName="id")
     */
    protected $backup;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="applicantTeamplates")
     * @ORM\JoinColumn(name="applicant_id", referencedColumnName="id")
     */
    protected $applicant;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="openedByTeamplates")
     * @ORM\JoinColumn(name="opened_by_id", referencedColumnName="id", nullable=true)
     */
    protected $openedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", nullable=true)
     */
    protected $token;

    /**
     * @var string
     *
     * @ORM\Column(name="tmp_configuration", type="string", nullable=true)
     */
    protected $tmpConfiguration;

    /**
     * @var boolean
     *
     * @ORM\Column(name="request_review", type="boolean", nullable=true)
     */
    protected $requestReview;

    /**
     * @ORM\Column(name="date_review", type="date", nullable=true)
     */
    protected $dateReview;




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
     * Set logbook
     *
     * @param boolean $logbook
     * @return TMTemplates
     */
    public function setLogbook($logbook)
    {
        $this->logbook = $logbook;

        return $this;
    }

    /**
     * Get logbook
     *
     * @return boolean 
     */
    public function getLogbook()
    {
        return $this->logbook;
    }

    /**
     * Set plantilla_id
     *
     * @param string $plantillaId
     * @return TMTemplates
     */
    public function setPlantillaId($plantillaId)
    {
        $this->plantilla_id = $plantillaId;

        return $this;
    }

    /**
     * Get plantilla_id
     *
     * @return string 
     */
    public function getPlantillaId()
    {
        return $this->plantilla_id;
    }

    /**
     * Set area
     *
     * @param \Nononsense\HomeBundle\Entity\Areas $area
     * @return TMTemplates
     */
    public function setArea(\Nononsense\HomeBundle\Entity\Areas $area = null)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return \Nononsense\HomeBundle\Entity\Areas 
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set QRType
     *
     * @param \Nononsense\HomeBundle\Entity\QrsTypes $qRType
     * @return TMTemplates
     */
    public function setQRType(\Nononsense\HomeBundle\Entity\QrsTypes $qRType = null)
    {
        $this->QRType = $qRType;

        return $this;
    }

    /**
     * Get QRType
     *
     * @return \Nononsense\HomeBundle\Entity\QrsTypes 
     */
    public function getQRType()
    {
        return $this->QRType;
    }

    /**
     * Set state
     *
     * @param \Nononsense\HomeBundle\Entity\TMStates $state
     * @return TMTemplates
     */
    public function setState(\Nononsense\HomeBundle\Entity\TMStates $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \Nononsense\HomeBundle\Entity\TMStates 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Add tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     * @return TMTemplates
     */
    public function addTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures[] = $tmSignatures;

        return $this;
    }

    /**
     * Remove tmSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures
     */
    public function removeTmSignature(\Nononsense\HomeBundle\Entity\TMSignatures $tmSignatures)
    {
        $this->tmSignatures->removeElement($tmSignatures);
    }

    /**
     * Get tmSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmSignatures()
    {
        return $this->tmSignatures;
    }

    /**
     * Set tmState
     *
     * @param \Nononsense\HomeBundle\Entity\TMStates $tmState
     * @return TMTemplates
     */
    public function setTmState(\Nononsense\HomeBundle\Entity\TMStates $tmState = null)
    {
        $this->tmState = $tmState;

        return $this;
    }

    /**
     * Get tmState
     *
     * @return \Nononsense\HomeBundle\Entity\TMStates 
     */
    public function getTmState()
    {
        return $this->tmState;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return TMTemplates
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix
     *
     * @return string 
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set template_id
     *
     * @param integer $templateId
     * @return TMTemplates
     */
    public function setTemplateId($templateId)
    {
        $this->template_id = $templateId;

        return $this;
    }

    /**
     * Get template_id
     *
     * @return integer 
     */
    public function getTemplateId()
    {
        return $this->template_id;
    }

    /**
     * Set numEdition
     *
     * @param integer $numEdition
     * @return TMTemplates
     */
    public function setNumEdition($numEdition)
    {
        $this->numEdition = $numEdition;

        return $this;
    }

    /**
     * Get numEdition
     *
     * @return integer 
     */
    public function getNumEdition()
    {
        return $this->numEdition;
    }

    /**
     * Add tmWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows
     * @return TMTemplates
     */
    public function addTmWorkflow(\Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows)
    {
        $this->tmWorkflows[] = $tmWorkflows;

        return $this;
    }

    /**
     * Remove tmWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows
     */
    public function removeTmWorkflow(\Nononsense\HomeBundle\Entity\TMWorkflow $tmWorkflows)
    {
        $this->tmWorkflows->removeElement($tmWorkflows);
    }

    /**
     * Get tmWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmWorkflows()
    {
        return $this->tmWorkflows;
    }

    /**
     * Set firstEdition
     *
     * @param integer $firstEdition
     * @return TMTemplates
     */
    public function setFirstEdition($firstEdition)
    {
        $this->firstEdition = $firstEdition;

        return $this;
    }

    /**
     * Get firstEdition
     *
     * @return integer 
     */
    public function getFirstEdition()
    {
        return $this->firstEdition;
    }

    /**
     * Add areas
     *
     * @param \Nononsense\HomeBundle\Entity\Areas $areas
     * @return TMTemplates
     */
    public function addArea(\Nononsense\HomeBundle\Entity\Areas $areas)
    {
        $this->areas[] = $areas;

        return $this;
    }

    /**
     * Remove areas
     *
     * @param \Nononsense\HomeBundle\Entity\Areas $areas
     */
    public function removeArea(\Nononsense\HomeBundle\Entity\Areas $areas)
    {
        $this->areas->removeElement($areas);
    }

    /**
     * Get areas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * Set historyChange
     *
     * @param string $historyChange
     * @return TMTemplates
     */
    public function setHistoryChange($historyChange)
    {
        $this->historyChange = $historyChange;

        return $this;
    }

    /**
     * Get historyChange
     *
     * @return string 
     */
    public function getHistoryChange()
    {
        return $this->historyChange;
    }

    /**
     * Set isSimple
     *
     * @param boolean $isSimple
     * @return TMTemplates
     */
    public function setIsSimple($isSimple)
    {
        $this->isSimple = $isSimple;

        return $this;
    }

    /**
     * Get isSimple
     *
     * @return boolean 
     */
    public function getIsSimple()
    {
        return $this->isSimple;
    }

    /**
     * Add retentions
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionCategories $retentions
     * @return TMTemplates
     */
    public function addRetention(\Nononsense\HomeBundle\Entity\RetentionCategories $retentions)
    {
        $this->retentions[] = $retentions;

        return $this;
    }

    /**
     * Remove retentions
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionCategories $retentions
     */
    public function removeRetention(\Nononsense\HomeBundle\Entity\RetentionCategories $retentions)
    {
        $this->retentions->removeElement($retentions);
    }

    /**
     * Get retentions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRetentions()
    {
        return $this->retentions;
    }

    /**
     * Set number
     *
     * @param string $number
     * @return TMTemplates
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string 
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set number_id
     *
     * @param integer $numberId
     * @return TMTemplates
     */
    public function setNumberId($numberId)
    {
        $this->number_id = $numberId;

        return $this;
    }

    /**
     * Get number_id
     *
     * @return integer 
     */
    public function getNumberId()
    {
        return $this->number_id;
    }

    /**
     * Set owner
     *
     * @param \Nononsense\UserBundle\Entity\Users $owner
     * @return TMTemplates
     */
    public function setOwner(\Nononsense\UserBundle\Entity\Users $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set backup
     *
     * @param \Nononsense\UserBundle\Entity\Users $backup
     * @return TMTemplates
     */
    public function setBackup(\Nononsense\UserBundle\Entity\Users $backup = null)
    {
        $this->backup = $backup;

        return $this;
    }

    /**
     * Get backup
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getBackup()
    {
        return $this->backup;
    }

    /**
     * Set reference
     *
     * @param string $reference
     * @return TMTemplates
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string 
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set applicant
     *
     * @param \Nononsense\UserBundle\Entity\Users $applicant
     * @return TMTemplates
     */
    public function setApplicant(\Nononsense\UserBundle\Entity\Users $applicant = null)
    {
        $this->applicant = $applicant;

        return $this;
    }

    /**
     * Get applicant
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getApplicant()
    {
        return $this->applicant;
    }

    /**
     * Set inactive
     *
     * @param boolean $inactive
     * @return TMTemplates
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;

        return $this;
    }

    /**
     * Get inactive
     *
     * @return boolean 
     */
    public function getInactive()
    {
        return $this->inactive;
    }

    /**
     * Set estimatedEffectiveDate
     *
     * @param \DateTime $estimatedEffectiveDate
     * @return TMTemplates
     */
    public function setEstimatedEffectiveDate($estimatedEffectiveDate)
    {
        $this->estimatedEffectiveDate = $estimatedEffectiveDate;

        return $this;
    }

    /**
     * Get estimatedEffectiveDate
     *
     * @return \DateTime 
     */
    public function getEstimatedEffectiveDate()
    {
        return $this->estimatedEffectiveDate;
    }

    /**
     * Set effectiveDate
     *
     * @param \DateTime $effectiveDate
     * @return TMTemplates
     */
    public function setEffectiveDate($effectiveDate)
    {
        $this->effectiveDate = $effectiveDate;

        return $this;
    }

    /**
     * Get effectiveDate
     *
     * @return \DateTime 
     */
    public function getEffectiveDate()
    {
        return $this->effectiveDate;
    }

    /**
     * Set reviewDate
     *
     * @param \DateTime $reviewDate
     * @return TMTemplates
     */
    public function setReviewDate($reviewDate)
    {
        $this->reviewDate = $reviewDate;

        return $this;
    }

    /**
     * Get reviewDate
     *
     * @return \DateTime 
     */
    public function getReviewDate()
    {
        return $this->reviewDate;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return TMTemplates
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
     * Set openedBy
     *
     * @param \Nononsense\UserBundle\Entity\Users $openedBy
     * @return TMTemplates
     */
    public function setOpenedBy(\Nononsense\UserBundle\Entity\Users $openedBy = null)
    {
        $this->openedBy = $openedBy;

        return $this;
    }

    /**
     * Get openedBy
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getOpenedBy()
    {
        return $this->openedBy;
    }

    /**
     * Set tmpConfiguration
     *
     * @param string $tmpConfiguration
     * @return TMTemplates
     */
    public function setTmpConfiguration($tmpConfiguration)
    {
        $this->tmpConfiguration = $tmpConfiguration;

        return $this;
    }

    /**
     * Get tmpConfiguration
     *
     * @return string 
     */
    public function getTmpConfiguration()
    {
        return $this->tmpConfiguration;
    }

    /**
     * Add tmSecondWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMSecondWorkflow $tmSecondWorkflows
     * @return TMTemplates
     */
    public function addTmSecondWorkflow(\Nononsense\HomeBundle\Entity\TMSecondWorkflow $tmSecondWorkflows)
    {
        $this->tmSecondWorkflows[] = $tmSecondWorkflows;

        return $this;
    }

    /**
     * Remove tmSecondWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\TMSecondWorkflow $tmSecondWorkflows
     */
    public function removeTmSecondWorkflow(\Nononsense\HomeBundle\Entity\TMSecondWorkflow $tmSecondWorkflows)
    {
        $this->tmSecondWorkflows->removeElement($tmSecondWorkflows);
    }

    /**
     * Get tmSecondWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmSecondWorkflows()
    {
        return $this->tmSecondWorkflows;
    }

    /**
     * Set uniqid
     *
     * @param boolean $uniqid
     * @return TMTemplates
     */
    public function setUniqid($uniqid)
    {
        $this->uniqid = $uniqid;

        return $this;
    }

    /**
     * Get uniqid
     *
     * @return boolean 
     */
    public function getUniqid()
    {
        return $this->uniqid;
    }

    /**
     * Add tmNestMasterTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMNestTemplates $tmNestMasterTemplates
     * @return TMTemplates
     */
    public function addTmNestMasterTemplate(\Nononsense\HomeBundle\Entity\TMNestTemplates $tmNestMasterTemplates)
    {
        $this->tmNestMasterTemplates[] = $tmNestMasterTemplates;

        return $this;
    }

    /**
     * Remove tmNestMasterTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMNestTemplates $tmNestMasterTemplates
     */
    public function removeTmNestMasterTemplate(\Nononsense\HomeBundle\Entity\TMNestTemplates $tmNestMasterTemplates)
    {
        $this->tmNestMasterTemplates->removeElement($tmNestMasterTemplates);
    }

    /**
     * Get tmNestMasterTemplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmNestMasterTemplates()
    {
        return $this->tmNestMasterTemplates;
    }

    /**
     * Add tmNestTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMNestTemplates $tmNestTemplates
     * @return TMTemplates
     */
    public function addTmNestTemplate(\Nononsense\HomeBundle\Entity\TMNestTemplates $tmNestTemplates)
    {
        $this->tmNestTemplates[] = $tmNestTemplates;

        return $this;
    }

    /**
     * Remove tmNestTemplates
     *
     * @param \Nononsense\HomeBundle\Entity\TMNestTemplates $tmNestTemplates
     */
    public function removeTmNestTemplate(\Nononsense\HomeBundle\Entity\TMNestTemplates $tmNestTemplates)
    {
        $this->tmNestTemplates->removeElement($tmNestTemplates);
    }

    /**
     * Get tmNestTemplates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmNestTemplates()
    {
        return $this->tmNestTemplates;
    }

    /**
     * Set requestReview
     *
     * @param integer $requestReview
     * @return TMTemplates
     */
    public function setRequestReview($requestReview)
    {
        $this->requestReview = $requestReview;

        return $this;
    }

    /**
     * Get requestReview
     *
     * @return integer 
     */
    public function getRequestReview()
    {
        return $this->requestReview;
    }

    /**
     * Set dateReview
     *
     * @param \DateTime $dateReview
     * @return TMTemplates
     */
    public function setDateReview($dateReview)
    {
        $this->dateReview = $dateReview;

        return $this;
    }

    /**
     * Get dateReview
     *
     * @return \DateTime 
     */
    public function getDateReview()
    {
        return $this->dateReview;
    }
}
