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
     * @Groups({"detail_document","json","detail_area"})
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=200)
     * @Assert\NotBlank(message = "You shoud insert a name")
     * @Groups({"detail_document","json","detail_area"})
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
     * @Groups({"detail_document","json"})
     */
    protected $number;

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
     * @var boolean $isSimple
     *
     * @ORM\Column(name="is_simple", type="boolean",  options={"default" = false})
     * @Groups({"detail_document"})
     */
    protected $isSimple;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Areas", mappedBy="template")
     */
    protected $areas;


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
}
