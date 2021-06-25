<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="cv_second_workflow")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\CVSecondWorkflowRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CVSecondWorkflow
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords", inversedBy="cvSecondWorkflows")
     * @ORM\JoinColumn(name="record_id", referencedColumnName="id")
     */
    protected $record;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="cvSecondWorkflows")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="cvSecondWorkflows")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected $group;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_signature", type="integer")
     */
    protected $numberSignature;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="finish_workflow", type="boolean",  options={"default" = false}, nullable=true)
     */
    protected $finishWorkflow;

    /**
     * @var boolean $signed
     *
     * @ORM\Column(name="signed", type="boolean",  options={"default" = 0})
     */
    protected $signed;

    
    public function __construct()
    {

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
     * Set signed
     *
     * @param boolean $signed
     * @return CVWorkflow
     */
    public function setSigned($signed)
    {
        $this->signed = $signed;

        return $this;
    }

    /**
     * Get signed
     *
     * @return boolean 
     */
    public function getSigned()
    {
        return $this->signed;
    }

    /**
     * Set record
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $record
     * @return CVWorkflow
     */
    public function setRecord(\Nononsense\HomeBundle\Entity\CVRecords $record = null)
    {
        $this->record = $record;

        return $this;
    }

    /**
     * Get record
     *
     * @return \Nononsense\HomeBundle\Entity\CVRecords 
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\TMCumplimentations $type
     * @return CVWorkflow
     */
    public function setType(\Nononsense\HomeBundle\Entity\TMCumplimentations $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\TMCumplimentations 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return CVWorkflow
     */
    public function setUser(\Nononsense\UserBundle\Entity\Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set group
     *
     * @param \Nononsense\GroupBundle\Entity\Groups $group
     * @return CVWorkflow
     */
    public function setGroup(\Nononsense\GroupBundle\Entity\Groups $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \Nononsense\GroupBundle\Entity\Groups 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set numberSignature
     *
     * @param integer $numberSignature
     * @return CVWorkflow
     */
    public function setNumberSignature($numberSignature)
    {
        $this->numberSignature = $numberSignature;

        return $this;
    }

    /**
     * Get numberSignature
     *
     * @return integer 
     */
    public function getNumberSignature()
    {
        return $this->numberSignature;
    }

    /**
     * Set finishWorkflow
     *
     * @param boolean $finishWorkflow
     * @return CVSecondWorkflow
     */
    public function setFinishWorkflow($finishWorkflow)
    {
        $this->finishWorkflow = $finishWorkflow;

        return $this;
    }

    /**
     * Get finishWorkflow
     *
     * @return boolean 
     */
    public function getFinishWorkflow()
    {
        return $this->finishWorkflow;
    }
}
