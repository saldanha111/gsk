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
 * @ORM\Table(name="archive_preservations")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ArchivePreservationsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ArchivePreservations
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
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveRecords", mappedBy="preservations")
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveSignatures", mappedBy="archivePreservation")
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
     * @return ArchivePreservations
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
     * @return ArchivePreservations
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
     * Set created
     *
     * @param \DateTime $created
     * @return ArchivePreservations
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
     * @return ArchivePreservations
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
     * @return ArchivePreservations
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
     * Add records
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $records
     * @return ArchivePreservations
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
     * @return ArchivePreservations
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
}
