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
 * @ORM\Table(name="revisionstep")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RevisionStepRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RevisionStep
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
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\InstanciasSteps", inversedBy="revisionStep")
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
     * @return RevisionStep
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
     * Set stepDataValue
     *
     * @param string $stepDataValue
     * @return RevisionStep
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
     * Set created
     *
     * @param \DateTime $created
     * @return RevisionStep
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
     * @return RevisionStep
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
     * Set stepEntity
     *
     * @param \Nononsense\HomeBundle\Entity\InstanciasSteps $stepEntity
     * @return RevisionStep
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
     * Set token
     *
     * @param string $token
     * @return RevisionStep
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
     * Set usage_id
     *
     * @param integer $usageId
     * @return RevisionStep
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
}
