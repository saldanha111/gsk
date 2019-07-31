<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 03/04/2018
 * Time: 13:51
 */

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="instanciassteps")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\InstanciasStepsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class InstanciasSteps
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
     * @ORM\Column(name="master_step_id", type="integer")
     */
    protected $master_step_id;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean",  nullable=true, options={"default" = false})
     */

    protected $isActive;

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
     * @var boolean $notification
     *
     * @ORM\Column(name="notification", type="boolean",  nullable=true, options={"default" = true})
     */
    protected $notification;

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
     * @var integer
     *
     * @ORM\Column(name="usage_id", type="integer")
     */
    protected $usage_id;

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
     * @ORM\Column(name="auxvalues", type="text")
     *
     */
    protected $auxvalues;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=90)
     */
    protected $token;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MasterSteps", inversedBy="InstanciasSteps")
     * @ORM\JoinColumn(name="master_step_id", referencedColumnName="id")
     */
    protected $master_step;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasWorkflows", inversedBy="Steps")
     * @ORM\JoinColumn(name="workflow_id", referencedColumnName="id")
     */
    protected $instancia_workflow;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\Revision", mappedBy="step")
     */
    protected $Revisions;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CancelacionStep", mappedBy="step")
     */
    protected $Cancelaciones;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RevisionStep", mappedBy="stepEntity")
     */
    protected $revisionStep;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\EvidenciasStep", mappedBy="stepEntity")
     */
    protected $evidenciasStep;


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
     * @return Instancias_Steps
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
     * Set master_step_id
     *
     * @param integer $masterStepId
     * @return Instancias_Steps
     */
    public function setMasterStepId($masterStepId)
    {
        $this->master_step_id = $masterStepId;

        return $this;
    }

    /**
     * Get master_step_id
     *
     * @return integer
     */
    public function getMasterStepId()
    {
        return $this->master_step_id;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Instancias_Steps
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
     * Set dependsOn
     *
     * @param integer $dependsOn
     * @return Instancias_Steps
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
     * @return Instancias_Steps
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
     * @return Instancias_Steps
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
     * Set status_id
     *
     * @param integer $statusId
     * @return Instancias_Steps
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
     * @return Instancias_Steps
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
     * @return Instancias_Steps
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
     * Set usage_id
     *
     * @param integer $usageId
     * @return Instancias_Steps
     */
    public function setUsageId($usageId)
    {
        $this->usage_id = $usageId;

        return $this;
    }

    /**
     * Get usage_id
     *
     * @return integer
     */
    public function getUsageId()
    {
        return $this->usage_id;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Instancias_Steps
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
     * Set master_step
     *
     * @param \Nononsense\HomeBundle\Entity\MasterSteps $masterStep
     * @return InstanciasSteps
     */
    public function setMasterStep(\Nononsense\HomeBundle\Entity\MasterSteps $masterStep = null)
    {
        $this->master_step = $masterStep;

        return $this;
    }

    /**
     * Get master_step
     *
     * @return \Nononsense\HomeBundle\Entity\MasterSteps
     */
    public function getMasterStep()
    {
        return $this->master_step;
    }

    /**
     * Get status String
     *
     * @return string
     */
    public function getStatus()
    {
        $return = "Indeterminado";
        if ($this->status_id == 1) {
            $return = "Enviado";
        } elseif ($this->status_id == 0) {
            $return = "Pendiente de rellenar";
        }


        return $return;
    }

    /**
     * Set instancia_workflow
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciaWorkflow
     * @return InstanciasSteps
     */
    public function setInstanciaWorkflow(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciaWorkflow = null)
    {
        $this->instancia_workflow = $instanciaWorkflow;

        return $this;
    }

    /**
     * Get instancia_workflow
     *
     * @return \Nononsense\HomeBundle\Entity\InstanciasWorkflows 
     */
    public function getInstanciaWorkflow()
    {
        return $this->instancia_workflow;
    }

    /**
     * Set stepDataValue
     *
     * @param string $stepDataValue
     * @return InstanciasSteps
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
     * Add Revisions
     *
     * @param \Nononsense\HomeBundle\Entity\Revision $revisions
     * @return InstanciasSteps
     */
    public function addRevision(\Nononsense\HomeBundle\Entity\Revision $revisions)
    {
        $this->Revisions[] = $revisions;

        return $this;
    }

    /**
     * Remove Revisions
     *
     * @param \Nononsense\HomeBundle\Entity\Revision $revisions
     */
    public function removeRevision(\Nononsense\HomeBundle\Entity\Revision $revisions)
    {
        $this->Revisions->removeElement($revisions);
    }

    /**
     * Get Revisions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRevisions()
    {
        return $this->Revisions;
    }

    /**
     * Set auxvalues
     *
     * @param string $auxvalues
     * @return InstanciasSteps
     */
    public function setAuxvalues($auxvalues)
    {
        $this->auxvalues = $auxvalues;

        return $this;
    }

    /**
     * Get auxvalues
     *
     * @return string 
     */
    public function getAuxvalues()
    {
        return $this->auxvalues;
    }


    /**
     * Add Cancelaciones
     *
     * @param \Nononsense\HomeBundle\Entity\CancelacionStep $cancelaciones
     * @return InstanciasSteps
     */
    public function addCancelacione(\Nononsense\HomeBundle\Entity\CancelacionStep $cancelaciones)
    {
        $this->Cancelaciones[] = $cancelaciones;

        return $this;
    }

    /**
     * Remove Cancelaciones
     *
     * @param \Nononsense\HomeBundle\Entity\CancelacionStep $cancelaciones
     */
    public function removeCancelacione(\Nononsense\HomeBundle\Entity\CancelacionStep $cancelaciones)
    {
        $this->Cancelaciones->removeElement($cancelaciones);
    }

    /**
     * Get Cancelaciones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCancelaciones()
    {
        return $this->Cancelaciones;
    }

    /**
     * Add revisionStep
     *
     * @param \Nononsense\HomeBundle\Entity\RevisionStep $revisionStep
     * @return InstanciasSteps
     */
    public function addRevisionStep(\Nononsense\HomeBundle\Entity\RevisionStep $revisionStep)
    {
        $this->revisionStep[] = $revisionStep;

        return $this;
    }

    /**
     * Remove revisionStep
     *
     * @param \Nononsense\HomeBundle\Entity\RevisionStep $revisionStep
     */
    public function removeRevisionStep(\Nononsense\HomeBundle\Entity\RevisionStep $revisionStep)
    {
        $this->revisionStep->removeElement($revisionStep);
    }

    /**
     * Get revisionStep
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRevisionStep()
    {
        return $this->revisionStep;
    }

    /**
     * Add evidenciasStep
     *
     * @param \Nononsense\HomeBundle\Entity\EvidenciasStep $evidenciasStep
     * @return InstanciasSteps
     */
    public function addEvidenciasStep(\Nononsense\HomeBundle\Entity\EvidenciasStep $evidenciasStep)
    {
        $this->evidenciasStep[] = $evidenciasStep;

        return $this;
    }

    /**
     * Remove evidenciasStep
     *
     * @param \Nononsense\HomeBundle\Entity\EvidenciasStep $evidenciasStep
     */
    public function removeEvidenciasStep(\Nononsense\HomeBundle\Entity\EvidenciasStep $evidenciasStep)
    {
        $this->evidenciasStep->removeElement($evidenciasStep);
    }

    /**
     * Get evidenciasStep
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvidenciasStep()
    {
        return $this->evidenciasStep;
    }
}
