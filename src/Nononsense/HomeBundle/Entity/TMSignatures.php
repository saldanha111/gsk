<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="tm_signatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\TMSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TMSignatures
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", inversedBy="tmSignatures")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMActions", inversedBy="tmSignatures")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     */
    protected $action;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTests", mappedBy="signature")
     */
    protected $tmTests;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", mappedBy="requestReview")
     */
    protected $templateReviews;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTests", inversedBy="tmSignatures")
     * @ORM\JoinColumn(name="test_id", referencedColumnName="id", nullable=true)
     */
    protected $tmTest;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTestAprob", inversedBy="tmSignatures")
     * @ORM\JoinColumn(name="aprob_action_id", referencedColumnName="id", nullable=true)
     */
    protected $tmAprobAction;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMWorkflow", inversedBy="tmSignatures")
     * @ORM\JoinColumn(name="who_aprob_from_workflow", referencedColumnName="id", nullable=true)
     */
    protected $tmWhoAprobFromWorkflow;

    /**
     * @var boolean $TmDropAction
     *
     * @ORM\Column(name="drop_action", type="boolean",  nullable=true)
     */
    protected $tmDropAction;


    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="text")
     */
    protected $signature;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="tmSignatures")
     * @ORM\JoinColumn(name="userid", referencedColumnName="id")
     */
    protected $userEntiy;

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
     * Set template
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $template
     * @return TMSignatures
     */
    public function setTemplate(\Nononsense\HomeBundle\Entity\TMTemplates $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \Nononsense\HomeBundle\Entity\TMTemplates 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set action
     *
     * @param \Nononsense\HomeBundle\Entity\TMActions $action
     * @return TMSignatures
     */
    public function setAction(\Nononsense\HomeBundle\Entity\TMActions $action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \Nononsense\HomeBundle\Entity\TMActions 
     */
    public function getAction()
    {
        return $this->action;
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
}
