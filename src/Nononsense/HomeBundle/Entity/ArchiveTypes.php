<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="archive_types")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ArchiveTypesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ArchiveTypes
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
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveRecords", mappedBy="type")
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveSignatures", mappedBy="archiveType")
     */
    protected $archiveSignatures;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->archive = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return ArchiveTypes
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
     * Add archive
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $archive
     * @return ArchiveTypes
     */
    public function addArchive(\Nononsense\HomeBundle\Entity\ArchiveRecords $archive)
    {
        $this->archive[] = $archive;

        return $this;
    }

    /**
     * Remove archive
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $archive
     */
    public function removeArchive(\Nononsense\HomeBundle\Entity\ArchiveRecords $archive)
    {
        $this->archive->removeElement($archive);
    }

    /**
     * Get archive
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ArchiveTypes
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
     * @return ArchiveTypes
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
     * @return ArchiveTypes
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
     * @return ArchiveTypes
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
     * @return ArchiveTypes
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
     * @return ArchiveTypes
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
