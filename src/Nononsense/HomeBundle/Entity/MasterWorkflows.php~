<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="masterworkflows")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MasterWorkflowsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MasterWorkflows
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
     * @var string
     *
     * @ORM\Column(name="masterdata", type="text")
     *
     */
    protected $masterData;

    /**
     * @var string
     *
     * @ORM\Column(name="config", type="text")
     *
     */
    protected $config;

    /**
     * @var string
     *
     * @ORM\Column(name="precreation", type="text")
     *
     */
    protected $precreation;

    /**
     * @var string
     *
     * @ORM\Column(name="validation", type="text")
     *
     */
    protected $validation;

    /**
     * @var string
     *
     * @ORM\Column(name="prevalidation", type="text")
     *
     */
    protected $prevalidation;

    /**
     * @var string
     *
     * @ORM\Column(name="ordersteps", type="text")
     *
     */
    protected $ordersteps;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean",  nullable=true, options={"default" = false})
     */

    protected $isActive;

    /**
     * @var boolean $logbook
     *
     * @ORM\Column(name="logbook", type="boolean",  options={"default" = false})
     */

    protected $logbook;

    /**
     * @var boolean $checklist
     *
     * @ORM\Column(name="checklist", type="boolean",  options={"default" = false})
     */

    protected $checklist;

    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer")
     */
    protected $category_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer")
     */
    protected $group_id;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Categories", inversedBy="Master_Workflows")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasWorkflows", mappedBy="Master_Workflow_Entity")
     */
    protected $Instancias;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MasterSteps", mappedBy="MasterWorkflow")
     */
    protected $MasterSteps;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="MasterWorkflowsVerificacion")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */

    protected $grupoVerificacion;

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
     * Set category_id
     *
     * @param integer $categoryId
     * @return Master_Workflows
     */
    public function setCategoryId($categoryId)
    {
        $this->category_id = $categoryId;

        return $this;
    }

    /**
     * Get category_id
     *
     * @return integer 
     */
    public function getCategoryId()
    {
        return $this->category_id;
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
     * Set category
     *
     * @param \Nononsense\HomeBundle\Entity\Categories $category
     * @return MasterWorkflows
     */
    public function setCategory(\Nononsense\HomeBundle\Entity\Categories $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Nononsense\HomeBundle\Entity\Categories 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add InstanciasWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflows
     * @return MasterWorkflows
     */
    public function addInstanciasWorkflow(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflows)
    {
        $this->InstanciasWorkflows[] = $instanciasWorkflows;

        return $this;
    }

    /**
     * Remove InstanciasWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflows
     */
    public function removeInstanciasWorkflow(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciasWorkflows)
    {
        $this->InstanciasWorkflows->removeElement($instanciasWorkflows);
    }

    /**
     * Get InstanciasWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstanciasWorkflows()
    {
        return $this->InstanciasWorkflows;
    }

    /**
     * Add MasterSteps
     *
     * @param \Nononsense\HomeBundle\Entity\MasterSteps $masterSteps
     * @return MasterWorkflows
     */
    public function addMasterStep(\Nononsense\HomeBundle\Entity\MasterSteps $masterSteps)
    {
        $this->MasterSteps[] = $masterSteps;

        return $this;
    }

    /**
     * Remove MasterSteps
     *
     * @param \Nononsense\HomeBundle\Entity\MasterSteps $masterSteps
     */
    public function removeMasterStep(\Nononsense\HomeBundle\Entity\MasterSteps $masterSteps)
    {
        $this->MasterSteps->removeElement($masterSteps);
    }

    /**
     * Get MasterSteps
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMasterSteps()
    {
        return $this->MasterSteps;
    }

    /**
     * Set alias
     *
     * @param string $alias
     * @return MasterWorkflows
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string 
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set tipoContrato
     *
     * @param string $tipoContrato
     * @return MasterWorkflows
     */
    public function setTipoContrato($tipoContrato)
    {
        $this->tipoContrato = $tipoContrato;

        return $this;
    }

    /**
     * Get tipoContrato
     *
     * @return string 
     */
    public function getTipoContrato()
    {
        return $this->tipoContrato;
    }

    /**
     * Set masterData
     *
     * @param string $masterData
     * @return MasterWorkflows
     */
    public function setMasterData($masterData)
    {
        $this->masterData = $masterData;

        return $this;
    }

    /**
     * Get masterData
     *
     * @return string 
     */
    public function getMasterData()
    {
        return $this->masterData;
    }

    /**
     * Set ordersteps
     *
     * @param string $ordersteps
     * @return MasterWorkflows
     */
    public function setOrdersteps($ordersteps)
    {
        $this->ordersteps = $ordersteps;

        return $this;
    }

    /**
     * Get ordersteps
     *
     * @return string 
     */
    public function getOrdersteps()
    {
        return $this->ordersteps;
    }

    /**
     * Add Instancias
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instancias
     * @return MasterWorkflows
     */
    public function addInstancia(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instancias)
    {
        $this->Instancias[] = $instancias;

        return $this;
    }

    /**
     * Remove Instancias
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instancias
     */
    public function removeInstancia(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instancias)
    {
        $this->Instancias->removeElement($instancias);
    }

    /**
     * Get Instancias
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstancias()
    {
        return $this->Instancias;
    }

    /**
     * Set config
     *
     * @param string $config
     * @return MasterWorkflows
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return string 
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set precreation
     *
     * @param string $precreation
     * @return MasterWorkflows
     */
    public function setPrecreation($precreation)
    {
        $this->precreation = $precreation;

        return $this;
    }

    /**
     * Get precreation
     *
     * @return string 
     */
    public function getPrecreation()
    {
        return $this->precreation;
    }

    /**
     * Set validation
     *
     * @param string $validation
     * @return MasterWorkflows
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Get validation
     *
     * @return string 
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Set prevalidation
     *
     * @param string $prevalidation
     * @return MasterWorkflows
     */
    public function setPrevalidation($prevalidation)
    {
        $this->prevalidation = $prevalidation;

        return $this;
    }

    /**
     * Get prevalidation
     *
     * @return string 
     */
    public function getPrevalidation()
    {
        return $this->prevalidation;
    }

    /**
     * Set group_id
     *
     * @param integer $groupId
     * @return MasterWorkflows
     */
    public function setGroupId($groupId)
    {
        $this->group_id = $groupId;

        return $this;
    }

    /**
     * Get group_id
     *
     * @return integer 
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Set grupoVerificacion
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $grupoVerificacion
     * @return MasterWorkflows
     */
    public function setGrupoVerificacion(\Nononsense\GroupBundle\Entity\Groups $grupoVerificacion = null)
    {
        $this->grupoVerificacion = $grupoVerificacion;

        return $this;
    }

    /**
     * Get grupoVerificacion
     *
     * @return \Nononsense\GroupBundle\Entity\Groups 
     */
    public function getGrupoVerificacion()
    {
        return $this->grupoVerificacion;
    }

    /**
     * Set logbook
     *
     * @param boolean $logbook
     * @return MasterWorkflows
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
     * Set checklist
     *
     * @param boolean $checklist
     * @return MasterWorkflows
     */
    public function setChecklist($checklist)
    {
        $this->checklist = $checklist;

        return $this;
    }

    /**
     * Get checklist
     *
     * @return boolean 
     */
    public function getChecklist()
    {
        return $this->checklist;
    }
}
