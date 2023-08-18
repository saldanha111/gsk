<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_departments")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanDepartmentsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanDepartments
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
     * @ORM\Column(name="name", type="string", length=255,  nullable=false, unique=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCenters", mappedBy="department")
     */
    private $center;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->active = 1;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MaterialCleanDepartments
     */
    public function setName(string $name): MaterialCleanDepartments
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set created
     *
     * @param DateTime $created
     * @return MaterialCleanDepartments
     */
    public function setCreated(DateTime $created): MaterialCleanDepartments
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * Set active
     *
     * @param bool $active
     * @return MaterialCleanDepartments
     */
    public function setActive(bool $active): MaterialCleanDepartments
    {
        $this->active = ($active)?: false;

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
     * Add center
     *
     * @param MaterialCleanCenters $center
     * @return MaterialCleanDepartments
     */
    public function addMaterial(MaterialCleanCenters $center): MaterialCleanDepartments
    {
        $this->center[] = $center;

        return $this;
    }

    /**
     * Remove center
     *
     * @param MaterialCleanCenters $center
     */
    public function removeMaterial(MaterialCleanCenters $center)
    {
        $this->center->removeElement($center);
    }

    /**
     * Get center
     *
     * @return Collection
     */
    public function getCenter(): Collection
    {
        return $this->center;
    }


    /**
     * Add center
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCenters $center
     * @return MaterialCleanDepartments
     */
    public function addCenter(\Nononsense\HomeBundle\Entity\MaterialCleanCenters $center)
    {
        $this->center[] = $center;

        return $this;
    }

    /**
     * Remove center
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanCenters $center
     */
    public function removeCenter(\Nononsense\HomeBundle\Entity\MaterialCleanCenters $center)
    {
        $this->center->removeElement($center);
    }
}
