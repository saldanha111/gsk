<?php
namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="archive_signatures")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ArchiveSignaturesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ArchiveSignatures
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveActions", inversedBy="archiveSignatures")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     */
    protected $archiveAction;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveCategories", inversedBy="archiveSignatures")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $archiveCategory;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchivePreservations", inversedBy="archiveSignatures")
     * @ORM\JoinColumn(name="preservation_id", referencedColumnName="id")
     */
    protected $archivePreservation;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveTypes", inversedBy="archiveSignatures")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $archiveType;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveRecords", inversedBy="archiveSignatures")
     * @ORM\JoinColumn(name="record_id", referencedColumnName="id")
     */
    protected $record;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveAZ", inversedBy="archiveSignatures")
     * @ORM\JoinColumn(name="az_id", referencedColumnName="id")
     */
    protected $archiveAz;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveLocations", inversedBy="archiveSignatures")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    protected $archiveLocation;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="archiveSignatures")
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

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveRecords", inversedBy="signatures")
     * @ORM\JoinTable(name="archive_signatures_records")
     */
    protected $records;

    /**
     * @var integer
     * @ORM\Column(name="groupid", type="integer", nullable=true)
     */
    protected $groupid;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment", type="text", nullable=true)
     */
    protected $attachment;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveSignatures")
     * @ORM\JoinColumn(name="patern_id", referencedColumnName="id")
     */
    protected $patern;

    /**
     * @var boolean
     *
     * @ORM\Column(name="not_available", type="boolean",  nullable=true)
     */
    protected $notAvailable;


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
     * @return ArchiveSignatures
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
     * @return ArchiveSignatures
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
     * Set groupId
     *
     * @param string $groupId
     * @return ArchiveSignatures
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

    /**
     * Set archiveAction
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveActions $archiveAction
     * @return ArchiveSignatures
     */
    public function setArchiveAction(\Nononsense\HomeBundle\Entity\ArchiveActions $archiveAction = null)
    {
        $this->archiveAction = $archiveAction;

        return $this;
    }

    /**
     * Get archiveAction
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveActions 
     */
    public function getArchiveAction()
    {
        return $this->archiveAction;
    }

    /**
     * Set archiveCategory
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveCategories $archiveCategory
     * @return ArchiveSignatures
     */
    public function setArchiveCategory(\Nononsense\HomeBundle\Entity\ArchiveCategories $archiveCategory = null)
    {
        $this->archiveCategory = $archiveCategory;

        return $this;
    }

    /**
     * Get archiveCategory
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveCategories 
     */
    public function getArchiveCategory()
    {
        return $this->archiveCategory;
    }

    /**
     * Set archivePreservation
     *
     * @param \Nononsense\HomeBundle\Entity\ArchivePreservations $archivePreservation
     * @return ArchiveSignatures
     */
    public function setArchivePreservation(\Nononsense\HomeBundle\Entity\ArchivePreservations $archivePreservation = null)
    {
        $this->archivePreservation = $archivePreservation;

        return $this;
    }

    /**
     * Get archivePreservation
     *
     * @return \Nononsense\HomeBundle\Entity\ArchivePreservations 
     */
    public function getArchivePreservation()
    {
        return $this->archivePreservation;
    }

    /**
     * Set record
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $record
     * @return ArchiveSignatures
     */
    public function setRecord(\Nononsense\HomeBundle\Entity\ArchiveRecords $record = null)
    {
        $this->record = $record;

        return $this;
    }

    /**
     * Get record
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveRecords 
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * Set userEntiy
     *
     * @param \Nononsense\UserBundle\Entity\Users $userEntiy
     * @return ArchiveSignatures
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
     * Set archiveType
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveTypes $archiveType
     * @return ArchiveSignatures
     */
    public function setArchiveType(\Nononsense\HomeBundle\Entity\ArchiveTypes $archiveType = null)
    {
        $this->archiveType = $archiveType;

        return $this;
    }

    /**
     * Get archiveType
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveTypes 
     */
    public function getArchiveType()
    {
        return $this->archiveType;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->records = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add records
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $records
     * @return ArchiveSignatures
     */
    public function addRecord(\Nononsense\HomeBundle\Entity\ArchiveRecords $records)
    {
        $this->records[] = $records;

        return $this;
    }

    /**
     * Remove records
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $records
     */
    public function removeRecord(\Nononsense\HomeBundle\Entity\ArchiveRecords $records)
    {
        $this->records->removeElement($records);
    }

    /**
     * Get records
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * Set attachment
     *
     * @param string $attachment
     * @return ArchiveSignatures
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * Get attachment
     *
     * @return string 
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * Set patern
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveSignatures $patern
     * @return ArchiveSignatures
     */
    public function setPatern(\Nononsense\HomeBundle\Entity\ArchiveSignatures $patern = null)
    {
        $this->patern = $patern;

        return $this;
    }

    /**
     * Get patern
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveSignatures 
     */
    public function getPatern()
    {
        return $this->patern;
    }

    /**
     * Set notAvailable
     *
     * @param boolean $notAvailable
     * @return ArchiveSignatures
     */
    public function setNotAvailable($notAvailable)
    {
        $this->notAvailable = $notAvailable;

        return $this;
    }

    /**
     * Get notAvailable
     *
     * @return boolean 
     */
    public function getNotAvailable()
    {
        return $this->notAvailable;
    }

    /**
     * Set archiveAz
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveAZ $archiveAz
     * @return ArchiveSignatures
     */
    public function setArchiveAz(\Nononsense\HomeBundle\Entity\ArchiveAZ $archiveAz = null)
    {
        $this->archiveAz = $archiveAz;

        return $this;
    }

    /**
     * Get archiveAz
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveAZ 
     */
    public function getArchiveAz()
    {
        return $this->archiveAz;
    }

    /**
     * Set archiveLocation
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveLocations $archiveLocation
     * @return ArchiveSignatures
     */
    public function setArchiveLocation(\Nononsense\HomeBundle\Entity\ArchiveLocations $archiveLocation = null)
    {
        $this->archiveLocation = $archiveLocation;

        return $this;
    }

    /**
     * Get archiveLocation
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveLocations 
     */
    public function getArchiveLocation()
    {
        return $this->archiveLocation;
    }
}
