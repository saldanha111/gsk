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
 * @ORM\Table(name="reconciliacionregistro")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\BloqueoMasterWorkflowRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ReconciliacionRegistro
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
     * @ORM\Column(name="registro_viejo_id", type="integer")
     */
    protected $registro_viejo_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="registro_nuevo_id", type="integer")
     */
    protected $registro_nuevo_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    protected $user_id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=200)
     */
    protected $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_validation", type="integer", nullable=true)
     */
    protected $user_validation;

    /**
     * @var string
     *
     * @ORM\Column(name="desc_validation", type="string", length=200, nullable=true)
     */
    protected $desc_validation;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="integer")
     */
    /*
     * 0: Iniciado
     * 1: Aceptado
     * 2: Rechazado
     */
    protected $status;


    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @var string
     *
     * @ORM\Column(name="txhash", type="string", length=255, nullable=true)
     */
    protected $txhash;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasWorkflows", inversedBy="ReconciliadoA")
     * @ORM\JoinColumn(name="registro_viejo_id", referencedColumnName="id")
     */
    protected $registroViejoEntity;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasWorkflows", inversedBy="ReconciliadoDe")
     * @ORM\JoinColumn(name="registro_nuevo_id", referencedColumnName="id")
     */
    protected $registroNuevoEntity;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="Reconciliaciones")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $userEntiy;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="ReconciliacionesValidations")
     * @ORM\JoinColumn(name="user_validation", referencedColumnName="id")
     */
    protected $userValidationEntiy;


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
     * Set registro_viejo_id
     *
     * @param integer $registroViejoId
     * @return ReconciliacionRegistro
     */
    public function setRegistroViejoId($registroViejoId)
    {
        $this->registro_viejo_id = $registroViejoId;

        return $this;
    }

    /**
     * Get registro_viejo_id
     *
     * @return integer 
     */
    public function getRegistroViejoId()
    {
        return $this->registro_viejo_id;
    }

    /**
     * Set registro_nuevo_id
     *
     * @param integer $registroNuevoId
     * @return ReconciliacionRegistro
     */
    public function setRegistroNuevoId($registroNuevoId)
    {
        $this->registro_nuevo_id = $registroNuevoId;

        return $this;
    }

    /**
     * Get registro_nuevo_id
     *
     * @return integer 
     */
    public function getRegistroNuevoId()
    {
        return $this->registro_nuevo_id;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ReconciliacionRegistro
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
     * Set status
     *
     * @param integer $status
     * @return ReconciliacionRegistro
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
     * @return ReconciliacionRegistro
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
     * @return ReconciliacionRegistro
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
     * Set user_id
     *
     * @param integer $userId
     * @return ReconciliacionRegistro
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set registroViejoEntity
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $registroViejoEntity
     * @return ReconciliacionRegistro
     */
    public function setRegistroViejoEntity(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $registroViejoEntity = null)
    {
        $this->registroViejoEntity = $registroViejoEntity;

        return $this;
    }

    /**
     * Get registroViejoEntity
     *
     * @return \Nononsense\HomeBundle\Entity\InstanciasWorkflows 
     */
    public function getRegistroViejoEntity()
    {
        return $this->registroViejoEntity;
    }

    /**
     * Set registroNuevoEntity
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasWorkflows $registroNuevoEntity
     * @return ReconciliacionRegistro
     */
    public function setRegistroNuevoEntity(\Nononsense\HomeBundle\Entity\InstanciasWorkflows $registroNuevoEntity = null)
    {
        $this->registroNuevoEntity = $registroNuevoEntity;

        return $this;
    }

    /**
     * Get registroNuevoEntity
     *
     * @return \Nononsense\HomeBundle\Entity\InstanciasWorkflows 
     */
    public function getRegistroNuevoEntity()
    {
        return $this->registroNuevoEntity;
    }

    /**
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return ReconciliacionRegistro
     */
    public function setUserEntiy(\Nononsense\UserBundle\Entity\Users $userEntiy = null)
    {
        $this->userEntiy = $userEntiy;

        return $this;
    }

    /**
     * Get userEntiy
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUserEntiy()
    {
        return $this->userEntiy;
    }

    /**
     * Set txhash
     *
     * @param string $txhash
     * @return ReconciliacionRegistro
     */
    public function setTxhash($txhash)
    {
        $this->txhash = $txhash;

        return $this;
    }

    /**
     * Get txhash
     *
     * @return string 
     */
    public function getTxhash()
    {
        return $this->txhash;
    }

    /**
     * Set user_validation
     *
     * @param integer $userValidation
     * @return ReconciliacionRegistro
     */
    public function setUserValidation($userValidation)
    {
        $this->user_validation = $userValidation;

        return $this;
    }

    /**
     * Get user_validation
     *
     * @return integer 
     */
    public function getUserValidation()
    {
        return $this->user_validation;
    }

    /**
     * Set desc_validation
     *
     * @param string $descValidation
     * @return ReconciliacionRegistro
     */
    public function setDescValidation($descValidation)
    {
        $this->desc_validation = $descValidation;

        return $this;
    }

    /**
     * Get desc_validation
     *
     * @return string 
     */
    public function getDescValidation()
    {
        return $this->desc_validation;
    }

    /**
     * Set userValidationEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userValidationEntiy
     * @return ReconciliacionRegistro
     */
    public function setUserValidationEntiy(\Nononsense\UserBundle\Entity\Users $userValidationEntiy = null)
    {
        $this->userValidationEntiy = $userValidationEntiy;

        return $this;
    }

    /**
     * Get userValidationEntiy
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUserValidationEntiy()
    {
        return $this->userValidationEntiy;
    }
}
