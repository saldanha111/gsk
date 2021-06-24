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
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVStates", inversedBy="cvRecords")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", nullable=true)
     */
    protected $state;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="cvRecords")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="cvRecordsGxP")
     * @ORM\JoinColumn(name="user_gxp_id", referencedColumnName="id")
     */
    protected $userGxP;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVWorkflow", mappedBy="record")
     */
    protected $cvWorkflows;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\CVSignatures", mappedBy="record")
     */
    protected $cvSignatures;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords")
     * @ORM\JoinColumn(name="reconciliation_id", referencedColumnName="id", nullable=true)
     */
    protected $reconciliation;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords")
     * @ORM\JoinColumn(name="first_reconciliation_id", referencedColumnName="id", nullable=true)
     */
    protected $firstReconciliation;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords")
     * @ORM\JoinColumn(name="nested_id", referencedColumnName="id", nullable=true)
     */
    protected $nested;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\CVRecords")
     * @ORM\JoinColumn(name="first_nested_id", referencedColumnName="id", nullable=true)
     */
    protected $firstNested;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @ORM\Column(name="open_date", type="datetime", nullable=true)
     */
    protected $openDate;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="in_edition", type="boolean",  options={"default" = false})
     */
    protected $inEdition;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="redirect_search", type="boolean", nullable=true)
     */
    protected $redirectSearch;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="enabled", type="boolean",  options={"default" = false})
     */
    protected $enabled;

    /**
     * @var string
     *
     * @ORM\Column(name="json", type="text", nullable=true)
     */
    protected $json;

    /**
     * @var string
     *
     * @ORM\Column(name="code_unique", type="text", nullable=true)
     */
    protected $codeUnique;

    /**
     * @var boolean 
     *
     * @ORM\Column(name="blocked", type="boolean", nullable=true)
     */
    protected $blocked;

    
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

    /**
     * Set state
     *
     * @param \Nononsense\HomeBundle\Entity\CVStates $state
     * @return CVRecords
     */
    public function setState(\Nononsense\HomeBundle\Entity\CVStates $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \Nononsense\HomeBundle\Entity\CVStates 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set json
     *
     * @param string $json
     * @return CVRecords
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
     * Set redirectSearch
     *
     * @param boolean $redirectSearch
     * @return CVRecords
     */
    public function setRedirectSearch($redirectSearch)
    {
        $this->redirectSearch = $redirectSearch;

        return $this;
    }

    /**
     * Get redirectSearch
     *
     * @return boolean 
     */
    public function getRedirectSearch()
    {
        return $this->redirectSearch;
    }

    /**
     * Set codeUnique
     *
     * @param string $codeUnique
     * @return CVRecords
     */
    public function setCodeUnique($codeUnique)
    {
        $this->codeUnique = $codeUnique;

        return $this;
    }

    /**
     * Get codeUnique
     *
     * @return string 
     */
    public function getCodeUnique()
    {
        return $this->codeUnique;
    }

    /**
     * Set reconciliation
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $reconciliation
     * @return CVRecords
     */
    public function setReconciliation(\Nononsense\HomeBundle\Entity\CVRecords $reconciliation = null)
    {
        $this->reconciliation = $reconciliation;

        return $this;
    }

    /**
     * Get reconciliation
     *
     * @return \Nononsense\HomeBundle\Entity\CVRecords 
     */
    public function getReconciliation()
    {
        return $this->reconciliation;
    }

    /**
     * Set firstReconciliation
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $firstReconciliation
     * @return CVRecords
     */
    public function setFirstReconciliation(\Nononsense\HomeBundle\Entity\CVRecords $firstReconciliation = null)
    {
        $this->firstReconciliation = $firstReconciliation;

        return $this;
    }

    /**
     * Get firstReconciliation
     *
     * @return \Nononsense\HomeBundle\Entity\CVRecords 
     */
    public function getFirstReconciliation()
    {
        return $this->firstReconciliation;
    }

    /**
     * Set nested
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $nested
     * @return CVRecords
     */
    public function setNested(\Nononsense\HomeBundle\Entity\CVRecords $nested = null)
    {
        $this->nested = $nested;

        return $this;
    }

    /**
     * Get nested
     *
     * @return \Nononsense\HomeBundle\Entity\CVRecords 
     */
    public function getNested()
    {
        return $this->nested;
    }

    /**
     * Set firstNested
     *
     * @param \Nononsense\HomeBundle\Entity\CVRecords $firstNested
     * @return CVRecords
     */
    public function setFirstNested(\Nononsense\HomeBundle\Entity\CVRecords $firstNested = null)
    {
        $this->firstNested = $firstNested;

        return $this;
    }

    /**
     * Get firstNested
     *
     * @return \Nononsense\HomeBundle\Entity\CVRecords 
     */
    public function getFirstNested()
    {
        return $this->firstNested;
    }

    /**
     * Set openDate
     *
     * @param \DateTime $openDate
     * @return CVRecords
     */
    public function setOpenDate($openDate)
    {
        $this->openDate = $openDate;

        return $this;
    }

    /**
     * Get openDate
     *
     * @return \DateTime 
     */
    public function getOpenDate()
    {
        return $this->openDate;
    }

    /**
     * Set blocked
     *
     * @param boolean $blocked
     * @return CVRecords
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * Get blocked
     *
     * @return boolean 
     */
    public function getBlocked()
    {
        return $this->blocked;
    }

    /**
     * Set userGxP
     *
     * @param \Nononsense\UserBundle\Entity\Users $userGxP
     * @return CVRecords
     */
    public function setUserGxP(\Nononsense\UserBundle\Entity\Users $userGxP = null)
    {
        $this->userGxP = $userGxP;

        return $this;
    }

    /**
     * Get userGxP
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getUserGxP()
    {
        return $this->userGxP;
    }
}
