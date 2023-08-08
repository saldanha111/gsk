<?php

namespace Nononsense\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="archive_locations")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\ArchiveLocationsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ArchiveLocations
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"location"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,  nullable=true)
     * @Groups({"location"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="building", type="string", length=255,  nullable=true)
     * @Groups({"location"})
     */
    protected $building;

    /**
     * @var string
     *
     * @ORM\Column(name="shelf", type="string", length=255,  nullable=true)
     * @Groups({"location"})
     */
    protected $shelf;

    /**
     * @var string
     *
     * @ORM\Column(name="passage", type="string", length=255,  nullable=true)
     * @Groups({"location"})
     */
    protected $passage;

    /**
     * @var string
     *
     * @ORM\Column(name="cabinet", type="string", length=255,  nullable=true)
     * @Groups({"location"})
     */
    protected $cabinet;

    /**
     * @var string
     *
     * @ORM\Column(name="others", type="string", length=255,  nullable=true)
     * @Groups({"location"})
     */
    protected $others;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveRecords", mappedBy="location")
     */
    protected $archive;

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
     * Set building
     *
     * @param string $building
     * @return ArchiveLocations
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * Get building
     *
     * @return string 
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * Set shelf
     *
     * @param string $shelf
     * @return ArchiveLocations
     */
    public function setShelf($shelf)
    {
        $this->shelf = $shelf;

        return $this;
    }

    /**
     * Get shelf
     *
     * @return string 
     */
    public function getShelf()
    {
        return $this->shelf;
    }

    /**
     * Set passage
     *
     * @param string $passage
     * @return ArchiveLocations
     */
    public function setPassage($passage)
    {
        $this->passage = $passage;

        return $this;
    }

    /**
     * Get passage
     *
     * @return string 
     */
    public function getPassage()
    {
        return $this->passage;
    }

    /**
     * Set cabinet
     *
     * @param string $cabinet
     * @return ArchiveLocations
     */
    public function setCabinet($cabinet)
    {
        $this->cabinet = $cabinet;

        return $this;
    }

    /**
     * Get cabinet
     *
     * @return string 
     */
    public function getCabinet()
    {
        return $this->cabinet;
    }

    /**
     * Set others
     *
     * @param string $others
     * @return ArchiveLocations
     */
    public function setOthers($others)
    {
        $this->others = $others;

        return $this;
    }

    /**
     * Get others
     *
     * @return string 
     */
    public function getOthers()
    {
        return $this->others;
    }

    /**
     * Add archive
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveRecords $archive
     * @return ArchiveLocations
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
     * Set name
     *
     * @param string $name
     * @return ArchiveLocations
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
}
