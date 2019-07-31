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
 * @ORM\Table(name="metadata")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MetaDataRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MetaData
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
     * @ORM\Column(name="workordersap", type="string", length=50,nullable=true)
     */
    protected $workordersap;

    /**
     * @var string
     *
     * @ORM\Column(name="equipo", type="string", length=50,nullable=true)
     */
    protected $equipo;

    /**
     * @var string
     *
     * @ORM\Column(name="lote", type="string", length=50,nullable=true)
     */
    protected $lote;

    /**
     * @var string
     *
     * @ORM\Column(name="material", type="string", length=50,nullable=true)
     */
    protected $material;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo_documento_lote", type="string", length=100,nullable=true)
     */
    protected $codigo_documento_lote;

    /**
     *
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $fechainicio;

    /**
     *
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $fechafin;


    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasWorkflows", inversedBy="metaData")
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
     * @return MetaData
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
     * Set created
     *
     * @param \DateTime $created
     * @return MetaData
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
     * @return MetaData
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
     * @return MetaData
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
     * Set workordersap
     *
     * @param string $workordersap
     * @return MetaData
     */
    public function setWorkordersap($workordersap)
    {
        $this->workordersap = $workordersap;

        return $this;
    }

    /**
     * Get workordersap
     *
     * @return string 
     */
    public function getWorkordersap()
    {
        return $this->workordersap;
    }

    /**
     * Set equipo
     *
     * @param string $equipo
     * @return MetaData
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
     * Set lote
     *
     * @param string $lote
     * @return MetaData
     */
    public function setLote($lote)
    {
        $this->lote = $lote;

        return $this;
    }

    /**
     * Get lote
     *
     * @return string 
     */
    public function getLote()
    {
        return $this->lote;
    }

    /**
     * Set material
     *
     * @param string $material
     * @return MetaData
     */
    public function setMaterial($material)
    {
        $this->material = $material;

        return $this;
    }

    /**
     * Get material
     *
     * @return string 
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Set fechainicio
     *
     * @param \DateTime $fechainicio
     * @return MetaData
     */
    public function setFechainicio($fechainicio)
    {
        $this->fechainicio = $fechainicio;

        return $this;
    }

    /**
     * Get fechainicio
     *
     * @return \DateTime 
     */
    public function getFechainicio()
    {
        return $this->fechainicio;
    }

    /**
     * Set fechafin
     *
     * @param \DateTime $fechafin
     * @return MetaData
     */
    public function setFechafin($fechafin)
    {
        $this->fechafin = $fechafin;

        return $this;
    }

    /**
     * Get fechafin
     *
     * @return \DateTime 
     */
    public function getFechafin()
    {
        return $this->fechafin;
    }

    /**
     * Set codigo_documento_lote
     *
     * @param string $codigoDocumentoLote
     * @return MetaData
     */
    public function setCodigoDocumentoLote($codigoDocumentoLote)
    {
        $this->codigo_documento_lote = $codigoDocumentoLote;

        return $this;
    }

    /**
     * Get codigo_documento_lote
     *
     * @return string 
     */
    public function getCodigoDocumentoLote()
    {
        return $this->codigo_documento_lote;
    }
}
