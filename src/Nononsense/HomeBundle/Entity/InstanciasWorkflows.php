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
 * @ORM\Table(name="instanciasworkflows")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\InstanciasWorkflowsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class InstanciasWorkflows
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
     * @ORM\Column(name="master_workflow", type="integer")
     */
    protected $master_workflow;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=90)
     */
    protected $description;

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
     * @ORM\Column(name="files", type="text")
     *
     */
    protected $files;

    /**
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @ORM\Column(name="fecha_firma" ,type="date",nullable=true)
     */
    protected $fecha_firma;

    /**
     * @ORM\Column(name="fecha_grab_borrador" ,type="datetime",nullable=true)
     */
    protected $fecha_grabado_borrador;
    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean",  nullable=true, options={"default" = false})
     */

    protected $isActive;

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
     * @ORM\Column(name="signvalues", type="text")
     *
     */
    protected $signvalues;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", options={"default" = 0})
     *
     */
    /*
     * -2 -> Recien creado
     * -1 -> Sin actividad
     * 0 -> En elaboracion
     * 1 -> Esperando firma guardado parcial
     * 2 -> Esperando firma para envío
     * 3 -> Esperando firma cancelación
     * 4 -> En verificación
     * 12 -> Esperando firma cancelacion en verificación
     * 13 -> Esperando firma devolución a edición
     * 14 -> Pendiente de cancelación en verificación
     * 5 -> Pendiente cancelación de edición
     * 6 -> Cancelado en edición
     * 7 -> Esperando firma verificación
     * 8 -> Cancelado
     * 9 -> Archivado
     * 10 -> Reconciliado
     * 11 -> Bloqueado
     * 17 ->Bloqueado- esperando ECO
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
     * @var integer
     *
     * @ORM\Column(name="departamento", type="integer", nullable=true,options={"default" = 1})
     */

    /*
     * 0 - desconocido
     * 1 - NACIONAL
     * 2 - SUPECO
     * null - (NACIONAL)
     */
    protected $departamento;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MasterWorkflows", inversedBy="Instancias")
     * @ORM\JoinColumn(name="master_workflow", referencedColumnName="id")
     */
    protected $Master_Workflow_Entity;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasSteps", mappedBy="instancia_workflow")
     */
    protected $Steps;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MetaData", mappedBy="instancia_workflow")
     */
    protected $metaData;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MetaFirmantes", mappedBy="instancia_workflow")
     */
    protected $metaFirmantes;

    /**
     * @var integer
     * @ORM\Column(name="usercreatedid", type="integer", options={"default" = 3})
     */
    protected $usercreatedid;


    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="InstanciasWorkflowCreated")
     * @ORM\JoinColumn(name="usercreatedid", referencedColumnName="id")
     */
    protected $userCreatedEntiy;


    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RevisionInstanciaWorkflow", mappedBy="instanciaWorkflowEntity")
     */
    protected $Revisions;


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
     * Set master_workflow
     *
     * @param integer $masterWorkflow
     * @return InstanciasWorkflows
     */
    public function setMasterWorkflow($masterWorkflow)
    {
        $this->master_workflow = $masterWorkflow;

        return $this;
    }

    /**
     * Get master_workflow
     *
     * @return integer
     */
    public function getMasterWorkflow()
    {
        return $this->master_workflow;
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
     * Set fecha_firma
     *
     * @param \DateTime $fechaFirma
     * @return InstanciasWorkflows
     */
    public function setFechaFirma($fechaFirma)
    {
        $this->fecha_firma = $fechaFirma;

        return $this;
    }

    /**
     * Get fecha_firma
     *
     * @return \DateTime
     */
    public function getFechaFirma()
    {
        return $this->fecha_firma;
    }

    /**
     * Set fecha_grabado_borrador
     *
     * @param \DateTime $fechaGrabadoBorrador
     * @return InstanciasWorkflows
     */
    public function setFechaGrabadoBorrador($fechaGrabadoBorrador)
    {
        $this->fecha_grabado_borrador = $fechaGrabadoBorrador;

        return $this;
    }

    /**
     * Get fecha_grabado_borrador
     *
     * @return \DateTime
     */
    public function getFechaGrabadoBorrador()
    {
        return $this->fecha_grabado_borrador;
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
     * Set signvalues
     *
     * @param string $signvalues
     * @return InstanciasWorkflows
     */
    public function setSignvalues($signvalues)
    {
        $this->signvalues = $signvalues;

        return $this;
    }

    /**
     * Get signvalues
     *
     * @return string
     */
    public function getSignvalues()
    {
        return $this->signvalues;
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

    public function getStatusString()
    {
        $result = "";
        switch ($this->status) {
            case -2:
                $result = "Iniciado sin datos";
                break;
            case -1:
                $result = "Iniciado con datos pre-cargados";
                break;
            case 0:
                $result = "Iniciado";
                break;
            case 1:
                $result = "Esperando firma guardado parcial";
                break;
            case 2:
                $result = "Esperando firma envío a verificación";
                break;
            case 3:
                $result = "Esperando firma cancelación";
                break;
            case 4:
                $result = "En verificación";
                break;
            case 5:
                $result = "Pendiente cancelación en edición";
                break;
            case 6:
                $result = "Cancelado en edición";
                break;
            case 7:
                $result = "Esperando firma verificación total";
                break;
            case 8:
                $result = "Cancelado";
                break;
            case 9:
                $result = "Archivado";
                break;
            case 10:
                $result = "Reconciliado";
                break;
            case 11:
                $result = "Bloqueado";
                break;
            case 12:
                $result = "Esperando firma cancelación en verificación";
                break;
            case 13:
                $result = "Esperando firma devolución a edición";
                break;
            case 14:
                $result = "Pendiente cancelación en verificación";
                break;
            case 15:
                $result = "Esperando firma verificación parcial";
                break;
            case 16:
                $result = "Esperando autorizacion para reconciliacion";
                break;
            case 17:
                $result = "Bloqueado, esperando ECO";
                break;


        }
        return $result;
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
     * Set departamento
     *
     * @param integer $departamento
     * @return InstanciasWorkflows
     */
    public function setDepartamento($departamento)
    {
        $this->departamento = $departamento;

        return $this;
    }

    /**
     * Get departamento
     *
     * @return integer
     */
    public function getDepartamento()
    {
        return $this->departamento;
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
     * Set Master_Workflow_Entity
     *
     * @param \Nononsense\HomeBundle\Entity\MasterWorkflows $masterWorkflowEntity
     * @return InstanciasWorkflows
     */
    public function setMasterWorkflowEntity(\Nononsense\HomeBundle\Entity\MasterWorkflows $masterWorkflowEntity = null)
    {
        $this->Master_Workflow_Entity = $masterWorkflowEntity;

        return $this;
    }

    /**
     * Get Master_Workflow_Entity
     *
     * @return \Nononsense\HomeBundle\Entity\MasterWorkflows
     */
    public function getMasterWorkflowEntity()
    {
        return $this->Master_Workflow_Entity;
    }

    /**
     * Add Steps
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasSteps $steps
     * @return InstanciasWorkflows
     */
    public function addStep(\Nononsense\HomeBundle\Entity\InstanciasSteps $steps)
    {
        $this->Steps[] = $steps;

        return $this;
    }

    /**
     * Remove Steps
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasSteps $steps
     */
    public function removeStep(\Nononsense\HomeBundle\Entity\InstanciasSteps $steps)
    {
        $this->Steps->removeElement($steps);
    }

    /**
     * Get Steps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSteps()
    {
        return $this->Steps;
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
     * Add metaFirmantes
     *
     * @param \Nononsense\HomeBundle\Entity\MetaFirmantes $metaFirmantes
     * @return InstanciasWorkflows
     */
    public function addMetaFirmante(\Nononsense\HomeBundle\Entity\MetaFirmantes $metaFirmantes)
    {
        $this->metaFirmantes[] = $metaFirmantes;

        return $this;
    }

    /**
     * Remove metaFirmantes
     *
     * @param \Nononsense\HomeBundle\Entity\MetaFirmantes $metaFirmantes
     */
    public function removeMetaFirmante(\Nononsense\HomeBundle\Entity\MetaFirmantes $metaFirmantes)
    {
        $this->metaFirmantes->removeElement($metaFirmantes);
    }

    /**
     * Get metaFirmantes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMetaFirmantes()
    {
        return $this->metaFirmantes;
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
     * Add Revisions
     *
     * @param \Nononsense\HomeBundle\Entity\RevisionInstanciaWorkflow $revisions
     * @return InstanciasWorkflows
     */
    public function addRevision(\Nononsense\HomeBundle\Entity\RevisionInstanciaWorkflow $revisions)
    {
        $this->Revisions[] = $revisions;

        return $this;
    }

    /**
     * Remove Revisions
     *
     * @param \Nononsense\HomeBundle\Entity\RevisionInstanciaWorkflow $revisions
     */
    public function removeRevision(\Nononsense\HomeBundle\Entity\RevisionInstanciaWorkflow $revisions)
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
}
