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
 * @ORM\Table(name="evidenciasstep")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\EvidenciasStepRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EvidenciasStep
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
     * @ORM\Column(name="step_id", type="integer")
     */
    protected $step_id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=90)
     */
    protected $token;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", options={"default" = 0})
     *
     */
    /*
     * 0 -> Completado
     * 1 -> Verificado
     * 3 -> Devuelto
     * 2 -> Editado
     * 5 -> Canceladion edicion
     * 6 -> Cancelacion verificacion
     * 7 -> Verificacion FLL
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="stepdatavalue", type="text")
     *
     */
    protected $stepDataValue;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="Evidencias")
     * @ORM\JoinColumn(name="usercreatedid", referencedColumnName="id")
     */
    protected $userEntiy;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\FirmasStep", inversedBy="Firma")
     * @ORM\JoinColumn(name="firmaid", referencedColumnName="id")
     */
    protected $firmaEntity;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasSteps", inversedBy="evidenciasStep")
     * @ORM\JoinColumn(name="step_id", referencedColumnName="id")
     */
    protected $stepEntity;
    
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
     * Set step_id
     *
     * @param integer $stepId
     * @return EvidenciasStep
     */
    public function setStepId($stepId)
    {
        $this->step_id = $stepId;

        return $this;
    }

    /**
     * Get step_id
     *
     * @return integer 
     */
    public function getStepId()
    {
        return $this->step_id;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return EvidenciasStep
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
     * Set status
     *
     * @param integer $status
     * @return EvidenciasStep
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
     * @return EvidenciasStep
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
     * @return EvidenciasStep
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
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return EvidenciasStep
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
     * Set stepEntity
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasSteps $stepEntity
     * @return EvidenciasStep
     */
    public function setStepEntity(\Nononsense\HomeBundle\Entity\InstanciasSteps $stepEntity = null)
    {
        $this->stepEntity = $stepEntity;

        return $this;
    }

    /**
     * Get stepEntity
     *
     * @return \Nononsense\HomeBundle\Entity\InstanciasSteps 
     */
    public function getStepEntity()
    {
        return $this->stepEntity;
    }

    /**
     * Set firmaEntity
     *
     * @param \Nononsense\HomeBundle\Entity\FirmasStep $firmaEntity
     * @return EvidenciasStep
     */
    public function setFirmaEntity(\Nononsense\HomeBundle\Entity\FirmasStep $firmaEntity = null)
    {
        $this->firmaEntity = $firmaEntity;

        return $this;
    }

    /**
     * Get firmaEntity
     *
     * @return \Nononsense\HomeBundle\Entity\FirmasStep 
     */
    public function getFirmaEntity()
    {
        return $this->firmaEntity;
    }

    /**
     * Set stepDataValue
     *
     * @param string $stepDataValue
     * @return EvidenciasStep
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
}
