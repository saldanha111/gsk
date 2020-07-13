<?php

namespace Nononsense\HomeBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mc_products")
 * @ORM\Entity(repositoryClass="Nononsense\HomeBundle\Entity\MaterialCleanProductsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialCleanProducts
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255,  nullable=false, unique=true)
     */
    protected $name;

    /**
     * @var int
     * @ORM\Column(name="tags_number", type="integer")
     */
    protected $tagsNumber;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", options={"default" : 1})
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="\Nononsense\HomeBundle\Entity\MaterialCleanMaterials", mappedBy="product")
     */
    protected $material;

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
     * @return MaterialCleanProducts
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
     * Set tagsNumber
     *
     * @param integer $tagsNumber
     * @return MaterialCleanProducts
     */
    public function setTagsNumber($tagsNumber)
    {
        $this->tagsNumber = $tagsNumber;

        return $this;
    }

    /**
     * Get tagsNumber
     *
     * @return integer 
     */
    public function getTagsNumber()
    {
        return $this->tagsNumber;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->material = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add material
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanMaterials $material
     * @return MaterialCleanProducts
     */
    public function addMaterial(\Nononsense\HomeBundle\Entity\MaterialCleanMaterials $material)
    {
        $this->material[] = $material;

        return $this;
    }

    /**
     * Remove material
     *
     * @param \Nononsense\HomeBundle\Entity\MaterialCleanMaterials $material
     */
    public function removeMaterial(\Nononsense\HomeBundle\Entity\MaterialCleanMaterials $material)
    {
        $this->material->removeElement($material);
    }

    /**
     * Get material
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return MaterialCleanProducts
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
}
