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
 * @ORM\Table(name="firmasstep")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\FirmasStepRepository")
 * @ORM\HasLifecycleCallbacks
 */
class FirmasStep
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
     * @ORM\Column(name="firma", type="text")
     */
    protected $firma;

    /**
     * @var string
     *
     * @ORM\Column(name="accion", type="text")
     */
    protected $accion;

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     */
    protected $number;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="Firmas")
     * @ORM\JoinColumn(name="usercreatedid", referencedColumnName="id")
     */
    protected $userEntiy;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\EvidenciasStep", mappedBy="firmaEntity")
     */
    protected $Firma;


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
     * @return FirmasStep
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
     * Set firma
     *
     * @param string $firma
     * @return FirmasStep
     */
    public function setFirma($firma)
    {
        $this->firma = $firma;

        return $this;
    }

    /**
     * Get firma
     *
     * @return string 
     */
    public function getFirma()
    {
        return $this->firma;
    }

    /**
     * Set accion
     *
     * @param string $accion
     * @return FirmasStep
     */
    public function setAccion($accion)
    {
        $this->accion = $accion;

        return $this;
    }

    /**
     * Get accion
     *
     * @return string 
     */
    public function getAccion()
    {
        return $this->accion;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return FirmasStep
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
     * @return FirmasStep
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
     * @return FirmasStep
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
     * @return FirmasStep
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
     * Add Firma
     *
     * @param \Nononsense\HomeBundle\Entity\EvidenciasStep $firma
     * @return FirmasStep
     */
    public function addFirma(\Nononsense\HomeBundle\Entity\EvidenciasStep $firma)
    {
        $this->Firma[] = $firma;

        return $this;
    }

    /**
     * Remove Firma
     *
     * @param \Nononsense\HomeBundle\Entity\EvidenciasStep $firma
     */
    public function removeFirma(\Nononsense\HomeBundle\Entity\EvidenciasStep $firma)
    {
        $this->Firma->removeElement($firma);
    }

    /**
     * Set number
     *
     * @param integer $number
     * @return FirmasStep
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer 
     */
    public function getNumber()
    {
        return $this->number;
    }
}
