<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="archive_states")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ArchiveStatesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ArchiveStates
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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveRecords", mappedBy="state")
     */
    protected $archive;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255,  nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false,  nullable=true)
     */
    protected $created;

    /**
     * @ORM\Column(name="modified", type="datetime", nullable=false,  nullable=true)
     */
    protected $modified;

    /**
     * @var bool
     * @ORM\Column(name="active", type="boolean", options={"default" : 1},  nullable=true)
     */
    protected $active;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveSignatures", mappedBy="archiveState")
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
     * @return ArchiveStates
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
     * @return ArchiveStates
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
     * @return ArchiveStates
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
     * @return ArchiveStates
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
     * @return ArchiveStates
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
     * @return ArchiveStates
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
     * Add archiveSignatures
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveSignatures $archiveSignatures
     * @return ArchiveStates
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
