<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_centers")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanCentersRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanCenters
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
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

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
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCodes", mappedBy="idCenter")
     */
    private $barcode;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanCleans", mappedBy="center")
     */
    private $cleans;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanMaterials", mappedBy="center")
     */
    protected $material;

    /**
     * @ORM\ManyToOne(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanDepartments", inversedBy="center")
     * @ORM\JoinColumn(name="department", referencedColumnName="id")
     */
    protected $department;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->active = 1;
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
     * @return MaterialCleanCenters
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
     * @return MaterialCleanCenters
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
     * @param DateTime $created
     * @return MaterialCleanCenters
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return MaterialCleanCenters
     */
    public function setActive($active)
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
     * Add barcode
     *
     * @param MaterialCleanCodes $barcode
     * @return MaterialCleanCenters
     */
    public function addBarcode(MaterialCleanCodes $barcode)
    {
        $this->barcode[] = $barcode;

        return $this;
    }

    /**
     * Remove barcode
     *
     * @param MaterialCleanCodes $barcode
     */
    public function removeBarcode(MaterialCleanCodes $barcode)
    {
        $this->barcode->removeElement($barcode);
    }

    /**
     * Get barcode
     *
     * @return Collection
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * Add cleans
     *
     * @param MaterialCleanCleans $cleans
     * @return MaterialCleanCenters
     */
    public function addClean(MaterialCleanCleans $cleans)
    {
        $this->cleans[] = $cleans;

        return $this;
    }

    /**
     * Remove cleans
     *
     * @param MaterialCleanCleans $cleans
     */
    public function removeClean(MaterialCleanCleans $cleans)
    {
        $this->cleans->removeElement($cleans);
    }

    /**
     * Get cleans
     *
     * @return Collection
     */
    public function getCleans()
    {
        return $this->cleans;
    }

    /**
     * Add material
     *
     * @param MaterialCleanMaterials $material
     * @return MaterialCleanCenters
     */
    public function addMaterial(MaterialCleanMaterials $material)
    {
        $this->material[] = $material;

        return $this;
    }

    /**
     * Remove material
     *
     * @param MaterialCleanMaterials $material
     */
    public function removeMaterial(MaterialCleanMaterials $material)
    {
        $this->material->removeElement($material);
    }

    /**
     * Get material
     *
     * @return Collection
     */
    public function getMaterial()
    {
        return $this->material;
    }


    /**
     * Set department
     *
     * @param MaterialCleanDepartments|null $department
     * @return MaterialCleanCenters
     */
    public function setDepartment(?MaterialCleanDepartments $department = null): MaterialCleanCenters
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return MaterialCleanDepartments
     */
    public function getDepartment(): ?MaterialCleanDepartments
    {
        return $this->department;
    }
}
