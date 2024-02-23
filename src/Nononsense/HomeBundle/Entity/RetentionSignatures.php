<?php
namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="retention_signatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RetentionSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RetentionSignatures
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\RetentionActions", inversedBy="retentionSignatures")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     */
    protected $retentionAction;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\RetentionCategories", inversedBy="retentionSignatures")
     * @ORM\JoinColumn(name="rc_id", referencedColumnName="id")
     */
    protected $retentionCategory;

    /**
     * @var integer
     *
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    protected $retentionTemplate;

    /**
     * @var integer
     *
     * @ORM\Column(name="record_id", type="integer", nullable=true)
     */
    protected $retentionRecord;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="retentionSignatures")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $userEntiy;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    /**
     * @var string
     *
     * @ORM\Column(name="group_id", type="string", length=90, nullable=true)
     */
    protected $groupId;

    public function __construct()
    {
        $this->modified = new DateTime();
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
     * Set description
     *
     * @param string $description
     * @return RetentionSignatures
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
     * Set modified
     *
     * @param \DateTime $modified
     * @return RetentionSignatures
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
     * Set retentionCategory
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionCategories $retentionCategory
     * @return RetentionSignatures
     */
    public function setRetentionCategory(\Nononsense\HomeBundle\Entity\RetentionCategories $retentionCategory = null)
    {
        $this->retentionCategory = $retentionCategory;

        return $this;
    }

    /**
     * Get retentionCategory
     *
     * @return \Nononsense\HomeBundle\Entity\RetentionCategories 
     */
    public function getRetentionCategory()
    {
        return $this->retentionCategory;
    }

    /**
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return RetentionSignatures
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
     * Set retentionAction
     *
     * @param \Nononsense\HomeBundle\Entity\RetentionActions $retentionAction
     * @return RetentionSignatures
     */
    public function setRetentionAction(\Nononsense\HomeBundle\Entity\RetentionActions $retentionAction = null)
    {
        $this->retentionAction = $retentionAction;

        return $this;
    }

    /**
     * Get retentionAction
     *
     * @return \Nononsense\HomeBundle\Entity\RetentionActions 
     */
    public function getRetentionAction()
    {
        return $this->retentionAction;
    }

    /**
     * Set retentionTemplate
     *
     * @param integer $retentionTemplate
     * @return RetentionSignatures
     */
    public function setRetentionTemplate($retentionTemplate)
    {
        $this->retentionTemplate = $retentionTemplate;

        return $this;
    }

    /**
     * Get retentionTemplate
     *
     * @return integer 
     */
    public function getRetentionTemplate()
    {
        return $this->retentionTemplate;
    }

    /**
     * Set retentionRecord
     *
     * @param integer $retentionRecord
     * @return RetentionSignatures
     */
    public function setRetentionRecord($retentionRecord)
    {
        $this->retentionRecord = $retentionRecord;

        return $this;
    }

    /**
     * Get retentionRecord
     *
     * @return integer 
     */
    public function getRetentionRecord()
    {
        return $this->retentionRecord;
    }

    /**
     * Set groupId
     *
     * @param string $groupId
     * @return RetentionSignatures
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId
     *
     * @return string 
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
}
