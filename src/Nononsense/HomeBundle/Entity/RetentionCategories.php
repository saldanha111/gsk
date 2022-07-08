<?php
/**
 * Created by IntelliJ IDEA.
 * User: ufarte
 * Date: 02/10/2020
 * Time: 12:27
 */

namespace Nononsense\HomeBundle\Entity;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UtilsBundle\Classes\Utils;

/**
 * @ORM\Entity
 * @ORM\Table(name="retention_categories")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\RetentionCategoriesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class RetentionCategories
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=90)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="retention_period_start_date", type="date")
     */
    protected $retentionPeriodStartDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="retention_period_end_date", type="date")
     */
    protected $retentionPeriodEndDate;

    /**
     * @var string
     *
     * @ORM\Column(name="retention_days", type="integer")
     */
    protected $retentionDays;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\RCTypes", inversedBy="rCategory")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\RCStates", inversedBy="category")
     * @ORM\JoinColumn(name="document_state", referencedColumnName="id")
     */
    protected $documentState;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="retention")
     * @ORM\JoinColumn(name="destroy_user", referencedColumnName="id")
     */
    protected $destroyUser;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\GroupBundle\Entity\Groups", inversedBy="retention")
     * @ORM\JoinColumn(name="destroy_group", referencedColumnName="id")
     */
    protected $destroyGroup;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @ORM\Column(name="modified", type="datetime", nullable=false)
     */
    protected $modified;

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\TMTemplates", mappedBy="retentions")
     */
        protected $templates;

    /**
     * @var bool
     * @ORM\Column(name="active", type="boolean", options={"default" : 1})
     */
    protected $active;

    /**
     * @var dateTime
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\RCSignatures", mappedBy="retentionCategory")
     */
    protected $rcSignatures;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->created = new DateTime();
        $this->templates = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return RetentionCategories
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return RetentionCategories
     */
    public function setDescription(string $description)
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
     * Set retentionDays
     *
     * @param DateTime $retentionPeriodStartDate
     * @return RetentionCategories
     * @throws \Exception
     */
    public function setRetentionPeriodStartDate(DateTime $retentionPeriodStartDate): RetentionCategories
    {
        $this->retentionPeriodStartDate = $retentionPeriodStartDate;

        return $this;
    }

    /**
     * Get retention period start date
     *
     * @return DateTime |null
     */
    public function getRetentionPeriodStartDate()
    {
        return $this->retentionPeriodStartDate;
    }

    /**
     * Get retention period end date
     *
     * @return DateTime |null
     */
    public function getRetentionPeriodEndDate()
    {
        return $this->retentionPeriodEndDate;
    }

    /**
     * Set retention period end date
     *
     * @return RetentionCategories
     * @throws \Exception
     */
    public function setRetentionPeriodEndDate(DateTime $startDate): RetentionCategories
    {
        $duration = "P" . $this->getRetentionDays() . "D";

        $this->retentionPeriodEndDate =  $startDate->add(new DateInterval($duration));

        return $this;
    }



    /**
     * Set retention period start date
     *
     * @param int $retentionDays
     * @return RetentionCategories
     */
    public function setRetentionDays(int $retentionDays): RetentionCategories
    {
        $this->retentionDays = $retentionDays;

        return $this;
    }



    /**
     * Get retentionDays
     *
     * @return int
     */
    public function getRetentionDays()
    {
        return $this->retentionDays;
    }

    /**
     * Set created
     *
     * @param DateTime $created
     * @return RetentionCategories
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param DateTime $modified
     * @return RetentionCategories
     */
    public function setModified(DateTime $modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set active
     *
     * @param bool $active
     * @return RetentionCategories
     */
    public function setActive(bool $active)
    {
        $this->active = ($active)?: false;

        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set type
     *
     * @param RCTypes|null $type
     * @return RetentionCategories
     */
    public function setType(RCTypes $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return RCTypes
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set documentState
     *
     * @param RCStates|null $documentState
     * @return RetentionCategories
     */
    public function setDocumentState(RCStates $documentState = null)
    {
        $this->documentState = $documentState;

        return $this;
    }

    /**
     * Get documentState
     *
     * @return RCStates
     */
    public function getDocumentState()
    {
        return $this->documentState;
    }

    /**
     * Set destroyUser
     *
     * @param Users|null $destroyUser
     * @return RetentionCategories
     */
    public function setDestroyUser(Users $destroyUser = null)
    {
        $this->destroyUser = $destroyUser;

        return $this;
    }

    /**
     * Get destroyUser
     *
     * @return Users
     */
    public function getDestroyUser()
    {
        return $this->destroyUser;
    }

    /**
     * Set destroyGroup
     *
     * @param Groups|null $destroyGroup
     * @return RetentionCategories
     */
    public function setDestroyGroup(Groups $destroyGroup = null)
    {
        $this->destroyGroup = $destroyGroup;

        return $this;
    }

    /**
     * Get destroyGroup
     *
     * @return Groups
     */
    public function getDestroyGroup()
    {
        return $this->destroyGroup;
    }

    /**
     * Add templates
     *
     * @param TMTemplates $templates
     * @return RetentionCategories
     */
    public function addTemplate(TMTemplates $templates)
    {
        $this->templates[] = $templates;

        return $this;
    }

    /**
     * Remove templates
     *
     * @param TMTemplates $templates
     */
    public function removeTemplate(TMTemplates $templates)
    {
        $this->templates->removeElement($templates);
    }

    /**
     * Get templates
     *
     * @return Collection
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Get RetentionDays Formatted
     *
     * @return array
     * @return Collection
     */
    public function getRetentionDaysFormatted()
    {
        $remainingDays = $this->retentionDays;
        $years = floor($remainingDays / 365);
        $remainingDays -= $years*365;
        $months = floor($remainingDays / 30);
        $remainingDays -= $months*30;
        $days = $remainingDays;

        return ['days' => $days, 'months' => $months, 'years' => $years];
    }

    /**
     * Add RetentionDays Formatted
     *
     * @param array $dmy
     * @return RetentionCategories
     */
    public function setRetentionDaysFormatted(array $dmy)
    {
        $days = ($dmy['days']) ?: 0;
        $months = ($dmy['months']) ?: 0;
        $years = ($dmy['years']) ?: 0;

        return $this->setRetentionDays($days + ($months * 30) + ($years * 365));
    }

    /**
     * Set deletedAt
     *
     * @param DateTime $deletedAt
     * @return RetentionCategories
     */
    public function setDeletedAt(DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add rcSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RCSignatures $rcSignatures
     * @return RetentionCategories
     */
    public function addRcSignature(\Nononsense\HomeBundle\Entity\RCSignatures $rcSignatures)
    {
        $this->rcSignatures[] = $rcSignatures;

        return $this;
    }

    /**
     * Remove rcSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\RCSignatures $rcSignatures
     */
    public function removeRcSignature(\Nononsense\HomeBundle\Entity\RCSignatures $rcSignatures)
    {
        $this->rcSignatures->removeElement($rcSignatures);
    }

    /**
     * Get rcSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRcSignatures()
    {
        return $this->rcSignatures;
    }
}
