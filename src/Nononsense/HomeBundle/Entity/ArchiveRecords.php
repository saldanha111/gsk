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
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveLocations", inversedBy="archive")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    protected $location;

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchiveCategories", mappedBy="records")
     */
    protected $categories;

    /**
     * @ORM\ManyToMany(targetEntity="\Nononsense\HomeBundle\Entity\ArchivePreservations", mappedBy="records")
     */
    protected $preservations;

    /**
     * @var string
     *
     * @ORM\Column(name="unique_number", type="string", length=150)
     */
    protected $uniqueNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=150)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="edition", type="string", length=150)
     */
    protected $edition;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->preservations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set location
     *
     * @param \Nononsense\HomeBundle\Entity\ArchiveLocations $location
     * @return ArchiveRecords
     */
    public function setLocation(\Nononsense\HomeBundle\Entity\ArchiveLocations $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return \Nononsense\HomeBundle\Entity\ArchiveLocations 
     */
    public function getLocation()
    {
        return $this->location;
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
}
