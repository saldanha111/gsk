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
}
