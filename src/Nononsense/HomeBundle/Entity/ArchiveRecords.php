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
 * @ORM\Table(name="archive_records")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ArchiveRecordsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ArchiveRecords
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\Areas", inversedBy="archive")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id")
     */
    protected $area;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveTypes", inversedBy="archive")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveStates", inversedBy="archive")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     */
    protected $state;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveUseStates", inversedBy="archive")
     * @ORM\JoinColumn(name="state_use_id", referencedColumnName="id")
     */
    protected $useState;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveAZ", inversedBy="records")
     * @ORM\JoinColumn(name="az_id", referencedColumnName="id")
     */
    protected $az;

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveCategories", inversedBy="records")
     * @ORM\JoinTable(name="archive_records_categories")
     */
    protected $categories;

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchivePreservations", inversedBy="records")
     * @ORM\JoinTable(name="archive_records_preservations")
     */
    protected $preservations;

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveSignatures", mappedBy="records")
     */
    protected $signatures;

    /**
     * @var string
     *
     * @ORM\Column(name="unique_number", type="string", length=150, unique=true)
     */
    protected $uniqueNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=150, nullable=true)
     */
    protected $link;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="edition", type="string", length=150, nullable=true)
     */
    protected $edition;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\UserBundle\Entity\Users", inversedBy="archiveRecords")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    protected $creator;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @ORM\Column(name="modified", type="datetime", nullable=false)
     */
    protected $modified;

    /**
     * @ORM\Column(name="init_retention", type="datetime", nullable=true)
     */
    protected $initRetention;

    /**
     * @ORM\Column(name="removed_at", type="datetime", nullable=true)
     */
    protected $removedAt;

    /**
     * @var bool
     * @ORM\Column(name="retention_revision", type="boolean", nullable=true)
     */
    protected $retentionRevision;

    /**
     * Constructor
     */
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
     * Set uniqueNumber
     *
     * @param string $uniqueNumber
     * @return ArchiveRecords
     */
    public function setUniqueNumber($uniqueNumber)
    {
        $this->uniqueNumber = $uniqueNumber;

        return $this;
    }

    /**
     * Get uniqueNumber
     *
     * @return string 
     */
    public function getUniqueNumber()
    {
        return $this->uniqueNumber;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ArchiveRecords
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set edition
     *
     * @param string $edition
     * @return ArchiveRecords
     */
    public function setEdition($edition)
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Get edition
     *
     * @return string 
     */
    public function getEdition()
    {
        return $this->edition;
    }

    /**
     * Set area
     *
     * @param \Nononsense\HomeBundle\Entity\Areas $area
     * @return ArchiveRecords
     */
    public function setArea(\Nononsense\HomeBundle\Entity\Areas $area = null)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return \Nononsense\HomeBundle\Entity\Areas 
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set type
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveTypes $type
     * @return ArchiveRecords
     */
    public function setType(\Nononsense\HomeBundle\Entity\ArchiveTypes $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveTypes 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set state
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveStates $state
     * @return ArchiveRecords
     */
    public function setState(\Nononsense\HomeBundle\Entity\ArchiveStates $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveStates 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set useState
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveUseStates $useState
     * @return ArchiveRecords
     */
    public function setUseState(\Nononsense\HomeBundle\Entity\ArchiveUseStates $useState = null)
    {
        $this->useState = $useState;

        return $this;
    }

    /**
     * Get useState
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveUseStates 
     */
    public function getUseState()
    {
        return $this->useState;
    }

    /**
     * Set creator
     *
     * @param \Nononsense\UserBundle\Entity\Users $creator
     * @return ArchiveRecords
     */
    public function setCreator(\Nononsense\UserBundle\Entity\Users $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \Nononsense\UserBundle\Entity\Users 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return ArchiveRecords
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
     * @return ArchiveRecords
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
     * Set initRetention
     *
     * @param \DateTime $initRetention
     * @return ArchiveRecords
     */
    public function setInitRetention($initRetention)
    {
        $this->initRetention = $initRetention;

        return $this;
    }

    /**
     * Get initRetention
     *
     * @return \DateTime 
     */
    public function getInitRetention()
    {
        return $this->initRetention;
    }

    /**
     * Add categories
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveCategories $categories
     * @return ArchiveRecords
     */
    public function addCategory(\Nononsense\HomeBundle\Entity\ArchiveCategories $categories)
    {
        $this->categories[] = $categories;

        return $this;
    }

    /**
     * Remove categories
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveCategories $categories
     */
    public function removeCategory(\Nononsense\HomeBundle\Entity\ArchiveCategories $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add preservations
     *
     * @param \Nononsense\HomeBundle\Entity\ArchivePreservations $preservations
     * @return ArchiveRecords
     */
    public function addPreservation(\Nononsense\HomeBundle\Entity\ArchivePreservations $preservations)
    {
        $this->preservations[] = $preservations;

        return $this;
    }

    /**
     * Remove preservations
     *
     * @param \Nononsense\HomeBundle\Entity\ArchivePreservations $preservations
     */
    public function removePreservation(\Nononsense\HomeBundle\Entity\ArchivePreservations $preservations)
    {
        $this->preservations->removeElement($preservations);
    }

    /**
     * Get preservations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPreservations()
    {
        return $this->preservations;
    }

    /**
     * Add signatures
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveSignatures $signatures
     * @return ArchiveRecords
     */
    public function addSignature(\Nononsense\HomeBundle\Entity\ArchiveSignatures $signatures)
    {
        $this->signatures[] = $signatures;

        return $this;
    }

    /**
     * Remove signatures
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveSignatures $signatures
     */
    public function removeSignature(\Nononsense\HomeBundle\Entity\ArchiveSignatures $signatures)
    {
        $this->signatures->removeElement($signatures);
    }

    /**
     * Get signatures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSignatures()
    {
        return $this->signatures;
    }

    /**
     * Set removedAt
     *
     * @param \DateTime $removedAt
     * @return ArchiveRecords
     */
    public function setRemovedAt($removedAt)
    {
        $this->removedAt = $removedAt;

        return $this;
    }

    /**
     * Get removedAt
     *
     * @return \DateTime 
     */
    public function getRemovedAt()
    {
        return $this->removedAt;
    }

    /**
     * Set retentionRevision
     *
     * @param boolean $retentionRevision
     * @return ArchiveRecords
     */
    public function setRetentionRevision($retentionRevision)
    {
        $this->retentionRevision = $retentionRevision;

        return $this;
    }

    /**
     * Get retentionRevision
     *
     * @return boolean 
     */
    public function getRetentionRevision()
    {
        return $this->retentionRevision;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return ArchiveRecords
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set az
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveAZ $az
     * @return ArchiveRecords
     */
    public function setAz(\Nononsense\HomeBundle\Entity\ArchiveAZ $az = null)
    {
        $this->az = $az;

        return $this;
    }

    /**
     * Get az
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveAZ 
     */
    public function getAz()
    {
        return $this->az;
    }
}
