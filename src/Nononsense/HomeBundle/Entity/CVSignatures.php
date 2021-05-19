<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="cv_signatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\CVSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CVSignatures
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords", inversedBy="cvSignatures")
     * @ORM\JoinColumn(name="record_id", referencedColumnName="id")
     */
    protected $record;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="cvSignatures")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVActions", inversedBy="cvSignatures")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     */
    protected $action;

    /**
     * @var string
     *
     * @ORM\Column(name="json", type="text", nullable=true)
     */
    protected $json;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", nullable=true)
     */
    protected $version;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="string", nullable=true)
     */
    protected $configuration;

    /**
     * @var boolean
     *
     * @ORM\Column(name="signed", type="boolean",  nullable=true)
     */
    protected $signed;

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
     *
     * @ORM\Column(name="number", type="integer", nullable=true)
     */
    protected $number;

    
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
     * Set created
     *
     * @param \DateTime $created
     * @return TMSignatures
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
     * @return TMSignatures
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
     * Set signature
     *
     * @param string $signature
     * @return TMSignatures
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string 
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set version
     *
     * @param string $version
     * @return TMSignatures
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set configuration
     *
     * @param string $configuration
     * @return TMSignatures
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get configuration
     *
     * @return string 
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return TMSignatures
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
     * Set description
     *
     * @param string $description
     * @return TMSignatures
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
     * Add tmTests
     *
     * @param \Nononsense\HomeBundle\Entity\TMTests $tmTests
     * @return TMSignatures
     */
    public function addTmTest(\Nononsense\HomeBundle\Entity\TMTests $tmTests)
    {
        $this->tmTests[] = $tmTests;

        return $this;
    }

    /**
     * Remove tmTests
     *
     * @param \Nononsense\HomeBundle\Entity\TMTests $tmTests
     */
    public function removeTmTest(\Nononsense\HomeBundle\Entity\TMTests $tmTests)
    {
        $this->tmTests->removeElement($tmTests);
    }

    /**
     * Get tmTests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTmTests()
    {
        return $this->tmTests;
    }

    /**
     * Set tmTest
     *
     * @param \Nononsense\HomeBundle\Entity\TMTests $tmTest
     * @return TMSignatures
     */
    public function setTmTest(\Nononsense\HomeBundle\Entity\TMTests $tmTest = null)
    {
        $this->tmTest = $tmTest;

        return $this;
    }

    /**
     * Get tmTest
     *
     * @return \Nononsense\HomeBundle\Entity\TMTests 
     */
    public function getTmTest()
    {
        return $this->tmTest;
    }

    /**
     * Set tmAprobAction
     *
     * @param \Nononsense\HomeBundle\Entity\TMTestAprob $tmAprobAction
     * @return TMSignatures
     */
    public function setTmAprobAction(\Nononsense\HomeBundle\Entity\TMTestAprob $tmAprobAction = null)
    {
        $this->tmAprobAction = $tmAprobAction;

        return $this;
    }

    /**
     * Get tmAprobAction
     *
     * @return \Nononsense\HomeBundle\Entity\TMTestAprob 
     */
    public function getTmAprobAction()
    {
        return $this->tmAprobAction;
    }

    /**
     * Set tmWhoAprobFromWorkflow
     *
     * @param \Nononsense\HomeBundle\Entity\TMWorkflow $tmWhoAprobFromWorkflow
     * @return TMSignatures
     */
    public function setTmWhoAprobFromWorkflow(\Nononsense\HomeBundle\Entity\TMWorkflow $tmWhoAprobFromWorkflow = null)
    {
        $this->tmWhoAprobFromWorkflow = $tmWhoAprobFromWorkflow;

        return $this;
    }

    /**
     * Get tmWhoAprobFromWorkflow
     *
     * @return \Nononsense\HomeBundle\Entity\TMWorkflow 
     */
    public function getTmWhoAprobFromWorkflow()
    {
        return $this->tmWhoAprobFromWorkflow;
    }

    /**
     * Set tmDropAction
     *
     * @param boolean $tmDropAction
     * @return TMSignatures
     */
    public function setTmDropAction($tmDropAction)
    {
        $this->tmDropAction = $tmDropAction;

        return $this;
    }

    /**
     * Get tmDropAction
     *
     * @return boolean 
     */
    public function getTmDropAction()
    {
        return $this->tmDropAction;
    }

    /**
     * Add templateReviews
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $templateReviews
     * @return TMSignatures
     */
    public function addTemplateReview(\Nononsense\HomeBundle\Entity\TMTemplates $templateReviews)
    {
        $this->templateReviews[] = $templateReviews;

        return $this;
    }

    /**
     * Remove templateReviews
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $templateReviews
     */
    public function removeTemplateReview(\Nononsense\HomeBundle\Entity\TMTemplates $templateReviews)
    {
        $this->templateReviews->removeElement($templateReviews);
    }

    /**
     * Get templateReviews
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTemplateReviews()
    {
        return $this->templateReviews;
    }

    /**
     * Set signed
     *
     * @param boolean $signed
     * @return CVSignatures
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
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\TMCumplimentations $type
     * @return CVSignatures
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
     * @return CVSignatures
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
     * Set record
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $record
     * @return CVSignatures
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
     * Set json
     *
     * @param string $json
     * @return CVSignatures
     */
    public function setJson($json)
    {
        $this->json = $json;

        return $this;
    }

    /**
     * Get json
     *
     * @return string 
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Set action
     *
     * @param \Nononsense\HomeBundle\Entity\CVActions $action
     * @return CVSignatures
     */
    public function setAction(\Nononsense\HomeBundle\Entity\CVActions $action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \Nononsense\HomeBundle\Entity\CVActions 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set number
     *
     * @param integer $number
     * @return CVSignatures
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
