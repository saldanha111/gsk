<?php
namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="cv_records")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\CVRecordsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CVRecords
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", inversedBy="cvRecords")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\TMStates", inversedBy="cvRecords")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", nullable=true)
     */
    protected $state;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="cvRecords")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVWorkflow", mappedBy="record")
     */
    protected $cvWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVSignatures", mappedBy="record")
     */
    protected $cvSignatures;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="in_edition", type="boolean",  options={"default" = false})
     */
    protected $inEdition;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="enabled", type="boolean",  options={"default" = false})
     */
    protected $enabled;

    
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
     * Set inEdition
     *
     * @param boolean $inEdition
     * @return CVRecords
     */
    public function setInEdition($inEdition)
    {
        $this->inEdition = $inEdition;

        return $this;
    }

    /**
     * Get inEdition
     *
     * @return boolean 
     */
    public function getInEdition()
    {
        return $this->inEdition;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return CVRecords
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set template
     *
     * @param \Nononsense\HomeBundle\Entity\TMTemplates $template
     * @return CVRecords
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
     * Add cvWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows
     * @return CVRecords
     */
    public function addCvWorkflow(\Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows)
    {
        $this->cvWorkflows[] = $cvWorkflows;

        return $this;
    }

    /**
     * Remove cvWorkflows
     *
     * @param \Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows
     */
    public function removeCvWorkflow(\Nononsense\HomeBundle\Entity\CVWorkflow $cvWorkflows)
    {
        $this->cvWorkflows->removeElement($cvWorkflows);
    }

    /**
     * Get cvWorkflows
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvWorkflows()
    {
        return $this->cvWorkflows;
    }

    /**
     * Set state
     *
     * @param \Nononsense\HomeBundle\Entity\TMStates $state
     * @return CVRecords
     */
    public function setState(\Nononsense\HomeBundle\Entity\TMStates $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \Nononsense\HomeBundle\Entity\TMStates 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set user
     *
     * @param \Nononsense\UserBundle\Entity\Users $user
     * @return CVRecords
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
     * Add cvSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures
     * @return CVRecords
     */
    public function addCvSignature(\Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures)
    {
        $this->cvSignatures[] = $cvSignatures;

        return $this;
    }

    /**
     * Remove cvSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures
     */
    public function removeCvSignature(\Nononsense\HomeBundle\Entity\CVSignatures $cvSignatures)
    {
        $this->cvSignatures->removeElement($cvSignatures);
    }

    /**
     * Get cvSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCvSignatures()
    {
        return $this->cvSignatures;
    }
}
