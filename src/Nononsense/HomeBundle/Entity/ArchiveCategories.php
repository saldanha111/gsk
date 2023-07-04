<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nononsense\GroupBundle\Entity\Groups;
use Nononsense\UserBundle\Entity\Users;

/**
 * @ORM\Entity
 * @ORM\Table(name="archive_categories")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ArchiveCategoriesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ArchiveCategories
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
     * @var string
     *
     * @ORM\Column(name="retention_days", type="integer")
     */
    protected $retentionDays;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveStates", inversedBy="category")
     * @ORM\JoinColumn(name="document_state", referencedColumnName="id")
     */
    protected $documentState;

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveRecords", mappedBy="categories")
     */
    protected $records;
    

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @ORM\Column(name="modified", type="datetime", nullable=false)
     */
    protected $modified;

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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveSignatures", mappedBy="archiveCategory")
     */
    protected $archiveSignatures;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->records = new \Doctrine\Common\Collections\ArrayCollection();
        $this->archiveSignatures = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return ArchiveCategories
     */
    public function setName($name)
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
     * @return ArchiveCategories
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
     * Set retentionDays
     *
     * @param integer $retentionDays
     * @return ArchiveCategories
     */
    public function setRetentionDays($retentionDays)
    {
        $this->retentionDays = $retentionDays;

        return $this;
    }

    /**
     * Get retentionDays
     *
     * @return integer 
     */
    public function getRetentionDays()
    {
        return $this->retentionDays;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return ArchiveCategories
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
     * @return ArchiveCategories
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
     * Set active
     *
     * @param boolean $active
     * @return ArchiveCategories
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return ArchiveCategories
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set documentState
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveStates $documentState
     * @return ArchiveCategories
     */
    public function setDocumentState(\Nononsense\HomeBundle\Entity\ArchiveStates $documentState = null)
    {
        $this->documentState = $documentState;

        return $this;
    }

    /**
     * Get documentState
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveStates 
     */
    public function getDocumentState()
    {
        return $this->documentState;
    }

    /**
     * Add records
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $records
     * @return ArchiveCategories
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
     * Add archiveSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures
     * @return ArchiveCategories
     */
    public function addArchiveSignature(\Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures)
    {
        $this->archiveSignatures[] = $archiveSignatures;

        return $this;
    }

    /**
     * Remove archiveSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures
     */
    public function removeArchiveSignature(\Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures)
    {
        $this->archiveSignatures->removeElement($archiveSignatures);
    }

    /**
     * Get archiveSignatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArchiveSignatures()
    {
        return $this->archiveSignatures;
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
     * @return ArchiveCategories
     */
    public function setRetentionDaysFormatted(array $dmy)
    {
        $days = ($dmy['days']) ?: 0;
        $months = ($dmy['months']) ?: 0;
        $years = ($dmy['years']) ?: 0;

        return $this->setRetentionDays($days + ($months * 30) + ($years * 365));
    }
}
