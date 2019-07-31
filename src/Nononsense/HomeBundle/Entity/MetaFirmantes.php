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
 * @ORM\Table(name="metafirmantes")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MetaFirmantesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MetaFirmantes
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100)
     */
    protected $email;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="date")
     */
    protected $modified;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasWorkflows", inversedBy="metaFirmantes")
     * @ORM\JoinColumn(name="workflow_id", referencedColumnName="id")
     */
    protected $instancia_workflow;

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
     * @return MetaFirmantes
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
     * Set name
     *
     * @param string $name
     * @return MetaFirmantes
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
     * Set created
     *
     * @param \DateTime $created
     * @return MetaFirmantes
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
     * @return MetaFirmantes
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
     * Set instancia_workflow
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciaWorkflow
     * @return MetaFirmantes
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
     * Set email
     *
     * @param string $email
     * @return MetaFirmantes
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
