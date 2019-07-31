<?php
/**
 * Created by IntelliJ IDEA.
 * User: gushe
 * Date: 01/06/2018
 * Time: 9:14
 */

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="revisioninstanciaworkflow")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RevisionInstanciaWorkflowRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RevisionInstanciaWorkflow
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
     * @ORM\Column(name="instanciaworkflowid", type="integer")
     */
    protected $instanciaworkflowid;

    /**
     * @var string
     *
     * @ORM\Column(name="revisiontext", type="text")
     */
    protected $revisiontext;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", options={"default" = 0})
     */
    protected $status;

    /**
     * @var integer
     * 0 borrador
     * 1 n1
     * 2 n2
     * 4 logistica
     *
     * @ORM\Column(name="type", type="integer", options={"default" = 0})
     */
    protected $type;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @var integer
     * @ORM\Column(name="userrevisionid", type="integer", options={"default" = 3}, nullable=true)
     */
    protected $userrevisionid;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="Revisions")
     * @ORM\JoinColumn(name="userrevisionid", referencedColumnName="id")
     */
    protected $userRevisionEntiy;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasWorkflows", inversedBy="Revisions")
     * @ORM\JoinColumn(name="instanciaworkflowid", referencedColumnName="id")
     */
    protected $instanciaWorkflowEntity;

    /**
     * Revision constructor.
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
     * Set revisiontext
     *
     * @param string $revisiontext
     * @return Revision
     */
    public function setRevisiontext($revisiontext)
    {
        $this->revisiontext = $revisiontext;

        return $this;
    }

    /**
     * Get revisiontext
     *
     * @return string 
     */
    public function getRevisiontext()
    {
        return $this->revisiontext;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Revision
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
     * Set created
     *
     * @param \DateTime $created
     * @return Revision
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
     * @return Revision
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
     * Set userrevisionid
     *
     * @param integer $userrevisionid
     * @return Revision
     */
    public function setUserrevisionid($userrevisionid)
    {
        $this->userrevisionid = $userrevisionid;

        return $this;
    }

    /**
     * Get userrevisionid
     *
     * @return integer 
     */
    public function getUserrevisionid()
    {
        return $this->userrevisionid;
    }

    /**
     * Set userRevisionEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userRevisionEntiy
     * @return Revision
     */
    public function setUserRevisionEntiy(\Nononsense\UserBundle\Entity\Users $userRevisionEntiy = null)
    {
        $this->userRevisionEntiy = $userRevisionEntiy;

        return $this;
    }

    /**
     * Get userRevisionEntiy
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUserRevisionEntiy()
    {
        return $this->userRevisionEntiy;
    }


    /**
     * Set instanciaworkflowid
     *
     * @param integer $instanciaworkflowid
     * @return RevisionInstanciaWorkflow
     */
    public function setInstanciaworkflowid($instanciaworkflowid)
    {
        $this->instanciaworkflowid = $instanciaworkflowid;

        return $this;
    }

    /**
     * Get instanciaworkflowid
     *
     * @return integer 
     */
    public function getInstanciaworkflowid()
    {
        return $this->instanciaworkflowid;
    }

    /**
     * Set instanciaWorkflowEntity
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciaWorkflowEntity
     * @return RevisionInstanciaWorkflow
     */
    public function setInstanciaWorkflowEntity(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $instanciaWorkflowEntity = null)
    {
        $this->instanciaWorkflowEntity = $instanciaWorkflowEntity;

        return $this;
    }

    /**
     * Get instanciaWorkflowEntity
     *
     * @return \Nononsense\HomeBundle\Entity\InstanciasWorkflows
     */
    public function getInstanciaWorkflowEntity()
    {
        return $this->instanciaWorkflowEntity;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return RevisionInstanciaWorkflow
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }
}
