<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="mastersteps")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MasterStepsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MasterSteps
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
     * @ORM\Column(name="workflow_id", type="integer")
     */
    protected $workflow_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="plantilla_id", type="integer")
     */
    protected $plantilla_id;

    /**
     * @var string
     *
     * @ORM\Column(name="plantilla_prefix", type="text")
     */
    protected $plantilla_prefix;

    /**
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer")
     */
    protected $group_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;
    
    /**
     * @var boolean $checklist
     *
     * @ORM\Column(name="checklist", type="boolean",  options={"default" = false})
     */

    protected $checklist;
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
     * @var string
     *
     * @ORM\Column(name="rules", type="text")
     */
    protected $rules;

    /**
     * @var string
     *
     * @ORM\Column(name="validation", type="text")
     */
    protected $validation;

    /**
     * @var boolean $notification
     *
     * @ORM\Column(name="notification", type="boolean",  nullable=true, options={"default" = true})
     */
    protected $notification;

    /**
     * @var boolean $cloneable
     *
     * @ORM\Column(name="cloneable", type="boolean",  nullable=true, options={"default" = false})
     */
    protected $cloneable;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=90)
     * @Assert\NotBlank(message = "You shoud insert a name")
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    protected $status_id;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @var string
     *
     * @ORM\Column(name="stepdata", type="text")
     *
     */
    protected $stepData;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasSteps", mappedBy="master_step")
     */
    protected $InstanciasSteps;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MasterWorkflows", inversedBy="MasterSteps")
     * @ORM\JoinColumn(name="workflow_id", referencedColumnName="id")
     */

    protected $MasterWorkflow;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="MasterSteps")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */

    protected $Groups;

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
     * Set workflow_id
     *
     * @param integer $workflowId
     * @return Master_Steps
     */
    public function setWorkflowId($workflowId)
    {
        $this->workflow_id = $workflowId;

        return $this;
    }

    /**
     * Get workflow_id
     *
     * @return integer 
     */
    public function getWorkflowId()
    {
        return $this->workflow_id;
    }

    /**
     * Set plantilla_id
     *
     * @param integer $plantillaId
     * @return Master_Steps
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
     * Get plantilla_id by Year
     *
     * @return integer
     */
    public function getPlantillaIdByYear($year)
    {
        $templatesString = $this->getPlantillaPrefix();
        $templatesJson = json_decode($templatesString);

        return $templatesJson->{$year};
    }

    /**
     * Set plantilla_prefix
     *
     * @param string $plantillaPrefix
     * @return Master_Steps
     */
    public function setPlantillaPrefix($plantillaPrefix)
    {
        $this->plantilla_prefix = $plantillaPrefix;

        return $this;
    }

    /**
     * Get plantilla_prefix
     *
     * @return string 
     */
    public function getPlantillaPrefix()
    {
        return $this->plantilla_prefix;
    }

    /**
     * Set group_id
     *
     * @param integer $groupId
     * @return Master_Steps
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
     * Set position
     *
     * @param integer $position
     * @return Master_Steps
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
     * Set dependsOn
     *
     * @param integer $dependsOn
     * @return Master_Steps
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
     * Set rules
     *
     * @param string $rules
     * @return Master_Steps
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Get rules
     *
     * @return string 
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Set notification
     *
     * @param boolean $notification
     * @return Master_Steps
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return boolean 
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Master_Steps
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
     * Set status_id
     *
     * @param integer $statusId
     * @return Master_Steps
     */
    public function setStatusId($statusId)
    {
        $this->status_id = $statusId;

        return $this;
    }

    /**
     * Get status_id
     *
     * @return integer 
     */
    public function getStatusId()
    {
        return $this->status_id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Master_Steps
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
     * @return Master_Steps
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
     * Add InstanciasSteps
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasSteps $instanciasSteps
     * @return MasterSteps
     */
    public function addInstanciasStep(\Nononsense\HomeBundle\Entity\InstanciasSteps $instanciasSteps)
    {
        $this->InstanciasSteps[] = $instanciasSteps;

        return $this;
    }

    /**
     * Remove InstanciasSteps
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasSteps $instanciasSteps
     */
    public function removeInstanciasStep(\Nononsense\HomeBundle\Entity\InstanciasSteps $instanciasSteps)
    {
        $this->InstanciasSteps->removeElement($instanciasSteps);
    }

    /**
     * Get InstanciasSteps
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstanciasSteps()
    {
        return $this->InstanciasSteps;
    }

    /**
     * Set MasterWorkflow
     *
     * @param \Nononsense\HomeBundle\Entity\MasterWorkflows $masterWorkflow
     * @return MasterSteps
     */
    public function setMasterWorkflow(\Nononsense\HomeBundle\Entity\MasterWorkflows $masterWorkflow = null)
    {
        $this->MasterWorkflow = $masterWorkflow;

        return $this;
    }

    /**
     * Get MasterWorkflow
     *
     * @return \Nononsense\HomeBundle\Entity\MasterWorkflows 
     */
    public function getMasterWorkflow()
    {
        return $this->MasterWorkflow;
    }

    /**
     * Set Groups
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $groups
     * @return MasterSteps
     */
    public function setGroups(\Nononsense\GroupBundle\Entity\Groups $groups = null)
    {
        $this->Groups = $groups;

        return $this;
    }

    /**
     * Get Groups
     *
     * @return \Nononsense\GroupBundle\Entity\Groups 
     */
    public function getGroups()
    {
        return $this->Groups;
    }

    /**
     * Set stepData
     *
     * @param string $stepData
     * @return MasterSteps
     */
    public function setStepData($stepData)
    {
        $this->stepData = $stepData;

        return $this;
    }

    /**
     * Get stepData
     *
     * @return string 
     */
    public function getStepData()
    {
        return $this->stepData;
    }

    /**
     * Set cloneable
     *
     * @param boolean $cloneable
     * @return MasterSteps
     */
    public function setCloneable($cloneable)
    {
        $this->cloneable = $cloneable;

        return $this;
    }

    /**
     * Get cloneable
     *
     * @return boolean 
     */
    public function getCloneable()
    {
        return $this->cloneable;
    }

    /**
     * Set block
     *
     * @param integer $block
     * @return MasterSteps
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
     * @return MasterSteps
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
     * Set validation
     *
     * @param string $validation
     * @return MasterSteps
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
     * Set checklist
     *
     * @param boolean $checklist
     * @return MasterSteps
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
