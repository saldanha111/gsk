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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveCategories", mappedBy="documentState")
     */
    protected $category;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveRecords", mappedBy="state")
     */
    protected $archive;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->category = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add category
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveCategories $category
     * @return ArchiveStates
     */
    public function addCategory(\Nononsense\HomeBundle\Entity\ArchiveCategories $category)
    {
        $this->category[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveCategories $category
     */
    public function removeCategory(\Nononsense\HomeBundle\Entity\ArchiveCategories $category)
    {
        $this->category->removeElement($category);
    }

    /**
     * Get category
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategory()
    {
        return $this->category;
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
}
