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
 * @ORM\Table(name="bloqueomasterworkflow")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\BloqueoMasterWorkflowRepository")
 * @ORM\HasLifecycleCallbacks
 */
class BloqueoMasterWorkflow
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
     * @ORM\Column(name="master_workflow_id", type="integer")
     */
    protected $master_workflow_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="registro_id", type="integer")
     */
    protected $registro_id;

    /**
     * @var string
     *
     * @ORM\Column(name="equipo", type="string", length=200)
     */
    protected $equipo;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", options={"default" = 0})
     *
     */
    /*
     * 0 -> Bloqueado
     * 1 -> Verificado
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $fechaInicioBloqueo;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;


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
     * Set master_workflow_id
     *
     * @param integer $masterWorkflowId
     * @return BloqueoMasterWorkflow
     */
    public function setMasterWorkflowId($masterWorkflowId)
    {
        $this->master_workflow_id = $masterWorkflowId;

        return $this;
    }

    /**
     * Get master_workflow_id
     *
     * @return integer 
     */
    public function getMasterWorkflowId()
    {
        return $this->master_workflow_id;
    }

    /**
     * Set equipo
     *
     * @param string $equipo
     * @return BloqueoMasterWorkflow
     */
    public function setEquipo($equipo)
    {
        $this->equipo = $equipo;

        return $this;
    }

    /**
     * Get equipo
     *
     * @return string 
     */
    public function getEquipo()
    {
        return $this->equipo;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return BloqueoMasterWorkflow
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
     * Set fechaInicioBloqueo
     *
     * @param \DateTime $fechaInicioBloqueo
     * @return BloqueoMasterWorkflow
     */
    public function setFechaInicioBloqueo($fechaInicioBloqueo)
    {
        $this->fechaInicioBloqueo = $fechaInicioBloqueo;

        return $this;
    }

    /**
     * Get fechaInicioBloqueo
     *
     * @return \DateTime 
     */
    public function getFechaInicioBloqueo()
    {
        return $this->fechaInicioBloqueo;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return BloqueoMasterWorkflow
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
     * @return BloqueoMasterWorkflow
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
     * Set registro_id
     *
     * @param integer $registroId
     * @return BloqueoMasterWorkflow
     */
    public function setRegistroId($registroId)
    {
        $this->registro_id = $registroId;

        return $this;
    }

    /**
     * Get registro_id
     *
     * @return integer 
     */
    public function getRegistroId()
    {
        return $this->registro_id;
    }
}
